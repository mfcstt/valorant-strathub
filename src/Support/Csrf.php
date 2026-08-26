<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Proteção contra Cross-Site Request Forgery pelo padrão synchronizer token.
 *
 * Um token aleatório vive na sessão e é embutido em todo formulário que altera
 * estado. Um site de terceiros consegue fazer o navegador da vítima enviar um
 * POST (os cookies vão junto), mas não consegue ler a sessão para descobrir o
 * token - então a requisição forjada falha na comparação.
 */
final class Csrf
{
    public const FIELD = '_token';

    private const SESSION_KEY = '_csrf_token';

    /**
     * Token da sessão atual, criado na primeira chamada.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Campo oculto pronto para inserir num formulário.
     */
    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::FIELD,
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8'),
        );
    }

    /**
     * Confere o token recebido em tempo constante.
     */
    public static function verify(?string $candidate): bool
    {
        $expected = $_SESSION[self::SESSION_KEY] ?? null;

        if (!is_string($expected) || $expected === '' || !is_string($candidate) || $candidate === '') {
            return false;
        }

        return hash_equals($expected, $candidate);
    }

    /**
     * Valida o token da requisição atual e aborta com 419 se não bater.
     *
     * 419 (convenção do Laravel para "Page Expired") comunica melhor que 403: o
     * caso mais comum não é ataque, é a sessão da pessoa ter expirado.
     */
    public static function check(): void
    {
        $candidate = $_POST[self::FIELD] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!self::verify(is_string($candidate) ? $candidate : null)) {
            abort(419, 'Sua sessão expirou. Recarregue a página e tente novamente.');
        }
    }

    /**
     * Gera um token novo - chamado no login e no logout, para que a troca de
     * identidade invalide tokens emitidos para a identidade anterior.
     */
    public static function rotate(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        self::token();
    }
}
