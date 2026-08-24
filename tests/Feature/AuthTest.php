<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Support\Auth;
use App\Support\Database;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * O token de "continuar conectado" precisa ser imprevisível e revogável.
 *
 * O mecanismo anterior era um cookie `auth_uid` acompanhado do HMAC do próprio
 * id, com o segredo embutido no código como valor padrão — bastava ler o
 * repositório para forjar o login de qualquer conta.
 */
final class AuthTest extends TestCase
{
    private Database $database;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = $this->useDatabase();

        $user = User::create('Jogador', 'jogador@strathub.test', 'senha#forte1', 'diamante');
        $this->assertNotNull($user);
        $this->user = $user;
    }

    #[Test]
    public function login_grava_o_usuario_na_sessao_sem_a_senha(): void
    {
        Auth::login($this->user, remember: false);

        $sessionUser = Auth::user();

        $this->assertNotNull($sessionUser);
        $this->assertSame((int) $this->user->id, $sessionUser->id);
        $this->assertSame('jogador@strathub.test', $sessionUser->email);
        // O hash da senha nunca deve circular na sessão.
        $this->assertFalse(property_exists($sessionUser, 'password'));
    }

    #[Test]
    public function login_com_remember_grava_apenas_o_hash_do_validator(): void
    {
        Auth::login($this->user, remember: true);

        $row = $this->database->first('SELECT selector, validator_hash FROM remember_tokens');

        $this->assertIsArray($row);

        [$selector, $validator] = explode(':', (string) $_COOKIE['strathub_remember'], 2);

        $this->assertSame($selector, $row['selector']);
        // O segredo em si não pode estar no banco: um dump da tabela não deve
        // permitir montar um cookie válido.
        $this->assertNotSame($validator, $row['validator_hash']);
        $this->assertSame(hash('sha256', $validator), $row['validator_hash']);
    }

    #[Test]
    public function cookie_valido_reidrata_a_sessao(): void
    {
        Auth::login($this->user, remember: true);
        $cookie = (string) $_COOKIE['strathub_remember'];

        // Simula uma requisição nova: sessão vazia, cookie presente.
        $_SESSION = [];
        $_COOKIE['strathub_remember'] = $cookie;
        $this->resetAuthCache();

        $this->assertTrue(Auth::check());
        $this->assertSame((int) $this->user->id, Auth::id());
    }

    #[Test]
    public function validator_errado_e_recusado_e_revoga_o_token(): void
    {
        Auth::login($this->user, remember: true);
        [$selector] = explode(':', (string) $_COOKIE['strathub_remember'], 2);

        $_SESSION = [];
        $_COOKIE['strathub_remember'] = $selector . ':' . str_repeat('f', 64);
        $this->resetAuthCache();

        $this->assertFalse(Auth::check());
        // Selector válido com validator errado é tentativa de adivinhação: o
        // token sai da tabela em vez de aceitar novas tentativas.
        $this->assertSame(0, (int) $this->database->scalar('SELECT COUNT(*) FROM remember_tokens'));
    }

    #[Test]
    public function cookie_forjado_a_partir_do_id_nao_autentica(): void
    {
        // Reprodução do ataque que o desenho antigo permitia.
        $_SESSION = [];
        $_COOKIE['strathub_remember'] = '1:' . hash_hmac('sha256', '1', 'strathub-fallback-secret');
        $this->resetAuthCache();

        $this->assertFalse(Auth::check());
    }

    #[Test]
    public function cookie_malformado_nao_autentica(): void
    {
        foreach (['', 'sem-separador', ':', 'selector:', ':validator'] as $value) {
            $_SESSION = [];
            $_COOKIE['strathub_remember'] = $value;
            $this->resetAuthCache();

            $this->assertFalse(Auth::check(), "Cookie '{$value}' não deveria autenticar.");
        }
    }

    #[Test]
    public function token_expirado_e_recusado(): void
    {
        Auth::login($this->user, remember: true);
        $cookie = (string) $_COOKIE['strathub_remember'];

        $this->database->execute(
            "UPDATE remember_tokens SET expires_at = :expired",
            ['expired' => date('Y-m-d H:i:s', time() - 3600)],
        );

        $_SESSION = [];
        $_COOKIE['strathub_remember'] = $cookie;
        $this->resetAuthCache();

        $this->assertFalse(Auth::check());
    }

    #[Test]
    public function logout_revoga_o_token_deste_dispositivo(): void
    {
        Auth::login($this->user, remember: true);
        $this->assertSame(1, (int) $this->database->scalar('SELECT COUNT(*) FROM remember_tokens'));

        Auth::logout();

        $this->assertFalse(Auth::check());
        $this->assertSame(0, (int) $this->database->scalar('SELECT COUNT(*) FROM remember_tokens'));
    }

    #[Test]
    public function revogar_todos_os_tokens_desconecta_os_outros_dispositivos(): void
    {
        Auth::login($this->user, remember: true);
        $this->resetAuthCache();
        Auth::login($this->user, remember: true);

        $this->assertSame(2, (int) $this->database->scalar('SELECT COUNT(*) FROM remember_tokens'));

        Auth::revokeAllTokensFor((int) $this->user->id);

        $this->assertSame(0, (int) $this->database->scalar('SELECT COUNT(*) FROM remember_tokens'));
    }

    #[Test]
    public function conta_apagada_invalida_o_token(): void
    {
        Auth::login($this->user, remember: true);
        $cookie = (string) $_COOKIE['strathub_remember'];

        User::delete((int) $this->user->id);

        $_SESSION = [];
        $_COOKIE['strathub_remember'] = $cookie;
        $this->resetAuthCache();

        $this->assertFalse(Auth::check());
    }

    #[Test]
    public function senha_e_guardada_como_hash(): void
    {
        $stored = (string) $this->database->scalar(
            'SELECT password FROM users WHERE id = :id',
            ['id' => $this->user->id],
        );

        $this->assertNotSame('senha#forte1', $stored);
        $this->assertTrue(password_verify('senha#forte1', $stored));
    }

    #[Test]
    public function verificacao_de_senha_recusa_valor_errado(): void
    {
        $this->assertTrue($this->user->verifyPassword('senha#forte1'));
        $this->assertFalse($this->user->verifyPassword('senha#errada1'));
    }

    #[Test]
    public function modo_visitante_nao_conta_como_autenticado(): void
    {
        Auth::enterGuestMode();
        $this->resetAuthCache();

        $this->assertFalse(Auth::check());
        $this->assertTrue(Auth::isGuest());
    }

    /**
     * Auth memoiza o usuário resolvido por requisição. Nos testes, cada asserção
     * representa uma requisição nova, então o cache precisa ser limpo.
     */
    private function resetAuthCache(): void
    {
        Auth::forgetResolvedUser();
    }
}
