<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * Renderização das views PHP.
 *
 * As views ficam em `src/Views`. O template base (`layouts/base.php`) inclui a
 * view pedida, que por sua vez pode incluir um componente. Nomes de view e de
 * componente vêm sempre do código da aplicação, nunca da requisição, mas são
 * validados de todo modo - é o tipo de invariante que custa uma linha para
 * garantir e caro para descobrir que faltava.
 */
final class View
{
    private static string $basePath = '';

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/');
    }

    public static function basePath(): string
    {
        if (self::$basePath === '') {
            self::$basePath = dirname(__DIR__) . '/Views';
        }

        return self::$basePath;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = [], ?string $component = null): void
    {
        self::guardName($view, 'view');

        if ($component !== null) {
            self::guardName($component, 'componente');
        }

        $viewPath = self::basePath() . "/{$view}.view.php";

        if (!is_file($viewPath)) {
            throw new RuntimeException("View não encontrada: {$view}");
        }

        // As variáveis do array ficam disponíveis por nome dentro da view.
        // EXTR_SKIP evita que um dado sobrescreva $view/$component/$data.
        extract($data, EXTR_SKIP);

        require self::basePath() . '/layouts/base.php';
    }

    /**
     * Escapa um valor para interpolação segura em HTML.
     */
    public static function escape(mixed $value): string
    {
        if ($value === null || is_bool($value)) {
            return '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function guardName(string $name, string $kind): void
    {
        if (preg_match('/^[A-Za-z0-9_-]+$/', $name) !== 1) {
            throw new RuntimeException("Nome de {$kind} inválido: {$name}");
        }
    }
}
