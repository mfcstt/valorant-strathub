<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use stdClass;

/**
 * Autenticação por sessão, com "continuar conectado" por token persistente.
 *
 * ## Por que existe um token persistente
 *
 * A aplicação roda em plataformas serverless, onde a sessão em arquivo do PHP
 * não sobrevive entre invocações. A versão anterior resolvia isso com um cookie
 * `auth_uid` acompanhado de um HMAC do próprio id — e o segredo tinha um valor
 * padrão embutido no código. Quem lesse o repositório conseguia calcular a
 * assinatura de qualquer id e entrar como qualquer usuário.
 *
 * ## O desenho atual (split-token)
 *
 * O cookie carrega `selector:validator`. O `selector` é um identificador público
 * usado para achar a linha no banco; o `validator` é o segredo, guardado apenas
 * como hash SHA-256. Isso dá três propriedades que o desenho anterior não tinha:
 *
 * - **Imprevisibilidade** — o validator é aleatório, não derivado do id.
 * - **Revogabilidade** — apagar a linha invalida o cookie de imediato.
 * - **Expiração real** — `expires_at` é conferido no servidor, não só no cookie.
 *
 * A busca é feita pelo selector (indexado) e a comparação do validator usa
 * `hash_equals`, para não vazar informação pelo tempo de resposta.
 */
final class Auth
{
    private const SESSION_KEY = 'auth';
    private const GUEST_KEY = 'guest';
    private const COOKIE = 'strathub_remember';
    private const LIFETIME_DAYS = 30;

    private static ?stdClass $cached = null;
    private static bool $resolved = false;

    /**
     * Usuário autenticado da requisição atual, ou null.
     */
    public static function user(): ?stdClass
    {
        if (self::$resolved) {
            return self::$cached;
        }
        self::$resolved = true;

        $session = $_SESSION[self::SESSION_KEY] ?? null;
        if ($session instanceof stdClass) {
            return self::$cached = $session;
        }

        return self::$cached = self::resolveFromRememberCookie();
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * Descarta o usuário memoizado, forçando nova resolução.
     *
     * Numa requisição real isso nunca é necessário — o cache vale por requisição.
     * Existe para os testes, onde uma única execução simula várias requisições.
     */
    public static function forgetResolvedUser(): void
    {
        self::$cached = null;
        self::$resolved = false;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return $user !== null ? (int) $user->id : null;
    }

    public static function isGuest(): bool
    {
        return !self::check() && !empty($_SESSION[self::GUEST_KEY]);
    }

    public static function enterGuestMode(): void
    {
        self::logout();
        $_SESSION[self::GUEST_KEY] = true;
    }

    /**
     * Registra o login e, opcionalmente, emite o token de "continuar conectado".
     */
    public static function login(User $user, bool $remember = true): void
    {
        // Trocar o id da sessão no momento da elevação de privilégio impede
        // fixação de sessão: um id que o atacante tenha plantado antes do login
        // deixa de ser válido exatamente quando passaria a valer algo.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        unset($_SESSION[self::GUEST_KEY]);

        $_SESSION[self::SESSION_KEY] = self::toSessionUser($user);
        self::$cached = $_SESSION[self::SESSION_KEY];
        self::$resolved = true;

        Csrf::rotate();

        if ($remember) {
            self::issueRememberToken((int) $user->id);
        }
    }

    /**
     * Encerra a sessão e revoga o token persistente deste dispositivo.
     */
    public static function logout(): void
    {
        self::revokeRememberToken();

        unset($_SESSION[self::SESSION_KEY], $_SESSION[self::GUEST_KEY]);
        self::$cached = null;
        self::$resolved = true;

        Csrf::rotate();
    }

    /**
     * Sincroniza a sessão depois de o perfil ser alterado.
     *
     * @param array<string, mixed> $attributes
     */
    public static function refresh(array $attributes): void
    {
        if (!isset($_SESSION[self::SESSION_KEY]) || !$_SESSION[self::SESSION_KEY] instanceof stdClass) {
            return;
        }

        foreach ($attributes as $key => $value) {
            $_SESSION[self::SESSION_KEY]->{$key} = $value;
        }

        self::$cached = $_SESSION[self::SESSION_KEY];
    }

    /**
     * Remove todos os tokens do usuário — usado ao trocar a senha, para
     * desconectar as outras sessões.
     */
    public static function revokeAllTokensFor(int $userId): void
    {
        Database::connection()->execute(
            'DELETE FROM remember_tokens WHERE user_id = :user_id',
            ['user_id' => $userId],
        );
    }

    /**
     * Projeta o model num objeto simples, sem a senha e sem a conexão PDO.
     */
    private static function toSessionUser(User $user): stdClass
    {
        $session = new stdClass();
        $session->id = (int) $user->id;
        $session->name = $user->name;
        $session->email = $user->email;
        $session->avatar = $user->avatar;
        $session->elo = $user->elo ?? 'ferro';
        $session->created_at = $user->created_at;
        $session->updated_at = $user->updated_at;

        return $session;
    }

    private static function resolveFromRememberCookie(): ?stdClass
    {
        $cookie = $_COOKIE[self::COOKIE] ?? null;

        if (!is_string($cookie) || !str_contains($cookie, ':')) {
            return null;
        }

        [$selector, $validator] = explode(':', $cookie, 2);

        if ($selector === '' || $validator === '') {
            return null;
        }

        $row = Database::connection()->first(
            'SELECT user_id, validator_hash, expires_at
               FROM remember_tokens
              WHERE selector = :selector',
            ['selector' => $selector],
        );

        if (!is_array($row)) {
            return null;
        }

        if (strtotime((string) $row['expires_at']) < time()) {
            self::deleteTokenBySelector($selector);
            self::clearCookie();

            return null;
        }

        if (!hash_equals((string) $row['validator_hash'], hash('sha256', $validator))) {
            // Selector válido com validator errado indica tentativa de adivinhação:
            // revoga o token em vez de permitir novas tentativas.
            self::deleteTokenBySelector($selector);
            self::clearCookie();

            return null;
        }

        $user = User::find((int) $row['user_id']);

        if ($user === null) {
            self::deleteTokenBySelector($selector);
            self::clearCookie();

            return null;
        }

        $session = self::toSessionUser($user);
        $_SESSION[self::SESSION_KEY] = $session;

        return $session;
    }

    private static function issueRememberToken(int $userId): void
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $expiresAt = time() + self::LIFETIME_DAYS * 86400;

        Database::connection()->execute(
            'INSERT INTO remember_tokens (user_id, selector, validator_hash, expires_at)
             VALUES (:user_id, :selector, :validator_hash, :expires_at)',
            [
                'user_id' => $userId,
                'selector' => $selector,
                'validator_hash' => hash('sha256', $validator),
                'expires_at' => date('Y-m-d H:i:s', $expiresAt),
            ],
        );

        self::sendCookie($selector . ':' . $validator, $expiresAt);
    }

    private static function revokeRememberToken(): void
    {
        $cookie = $_COOKIE[self::COOKIE] ?? null;

        if (is_string($cookie) && str_contains($cookie, ':')) {
            [$selector] = explode(':', $cookie, 2);
            self::deleteTokenBySelector($selector);
        }

        self::clearCookie();
    }

    private static function deleteTokenBySelector(string $selector): void
    {
        Database::connection()->execute(
            'DELETE FROM remember_tokens WHERE selector = :selector',
            ['selector' => $selector],
        );
    }

    private static function sendCookie(string $value, int $expiresAt): void
    {
        if (headers_sent()) {
            return;
        }

        setcookie(self::COOKIE, $value, self::cookieOptions($expiresAt));
        $_COOKIE[self::COOKIE] = $value;
    }

    private static function clearCookie(): void
    {
        if (!headers_sent()) {
            setcookie(self::COOKIE, '', self::cookieOptions(time() - 3600));
        }

        unset($_COOKIE[self::COOKIE]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function cookieOptions(int $expiresAt): array
    {
        return [
            'expires' => $expiresAt,
            'path' => '/',
            'secure' => Config::isProduction() || !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }
}
