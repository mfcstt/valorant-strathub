<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Decisões sobre URLs que precisam ser testáveis isoladamente.
 */
final class Url
{
    /**
     * Devolve o caminho se ele for interno e seguro; caso contrário, o fallback.
     *
     * Redirecionar para um destino vindo da requisição sem esta checagem cria uma
     * open redirect: o atacante manda um link que começa no domínio confiável e
     * termina numa página de phishing. As condições, em ordem:
     *
     * - precisa começar com `/` — descarta `https://...` e `javascript:...`;
     * - não pode começar com `//` — essa forma é interpretada como
     *   "mesmo protocolo, outro host", ou seja, ainda é externa;
     * - não pode conter CR ou LF — evita injeção de cabeçalho na resposta.
     */
    public static function safeInternalPath(?string $candidate, string $fallback = '/explore'): string
    {
        if (!is_string($candidate)) {
            return $fallback;
        }

        $path = trim($candidate);

        if ($path === '' || !str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return $fallback;
        }

        if (str_contains($path, "\n") || str_contains($path, "\r") || str_contains($path, "\0")) {
            return $fallback;
        }

        return $path;
    }
}
