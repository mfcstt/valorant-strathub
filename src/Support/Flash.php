<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Mensagens de uso único guardadas na sessão.
 *
 * O ponto central de uma flash message é durar exatamente uma requisição. A
 * implementação anterior nunca removia a chave da sessão, então mensagens e
 * dados de formulário reapareciam em toda navegação seguinte. Aqui `get()`
 * consome o valor; use `peek()` quando precisar ler sem consumir.
 */
final class Flash
{
    private const PREFIX = 'flash_';

    public function put(string $key, mixed $value): void
    {
        $_SESSION[self::PREFIX . $key] = $value;
    }

    /**
     * Lê e remove o valor.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $storageKey = self::PREFIX . $key;

        if (!array_key_exists($storageKey, $_SESSION)) {
            return $default;
        }

        $value = $_SESSION[$storageKey];
        unset($_SESSION[$storageKey]);

        return $value;
    }

    /**
     * Lê sem remover - útil quando a mesma chave é consultada mais de uma vez
     * na renderização de uma única página.
     */
    public function peek(string $key, mixed $default = null): mixed
    {
        return $_SESSION[self::PREFIX . $key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[self::PREFIX . $key]);
    }

    public function forget(string $key): void
    {
        unset($_SESSION[self::PREFIX . $key]);
    }

    /**
     * Remove todas as flash messages pendentes.
     */
    public function clear(): void
    {
        foreach (array_keys($_SESSION) as $key) {
            if (is_string($key) && str_starts_with($key, self::PREFIX)) {
                unset($_SESSION[$key]);
            }
        }
    }
}
