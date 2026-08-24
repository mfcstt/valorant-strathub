<?php

declare(strict_types=1);

/**
 * Entrega de arquivos estáticos pelo front controller.
 *
 * Só necessário em hospedagens que roteiam toda requisição para o PHP. A defesa
 * contra path traversal não depende de inspecionar a string em busca de `..`:
 * o caminho é resolvido com `realpath()` e comparado com o diretório permitido,
 * então qualquer forma de escapar (`..`, links simbólicos, codificações) resulta
 * num caminho fora do prefixo e é recusada.
 */

if (!function_exists('serve_static_asset')) {
    /**
     * Serve o arquivo pedido e encerra a requisição; retorna se não houver o que servir.
     */
    function serve_static_asset(string $publicPath): void
    {
        /** @var array<string, string> Extensão => Content-Type */
        static $contentTypes = [
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'ico' => 'image/x-icon',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
        ];

        /** @var list<string> Diretórios de onde é permitido servir */
        static $allowedDirectories = ['CSS', 'JS', 'assets', 'fonts', 'vendor'];

        $requested = $_GET['path'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '';
        $requested = ltrim(rawurldecode((string) $requested), '/');

        if ($requested === '') {
            return;
        }

        $topLevel = explode('/', $requested)[0];
        if (!in_array($topLevel, $allowedDirectories, true)) {
            return;
        }

        $root = realpath($publicPath);
        $resolved = realpath($publicPath . '/' . $requested);

        // O arquivo tem de existir e estar realmente dentro de public/.
        if ($root === false || $resolved === false || !is_file($resolved)) {
            return;
        }

        if (!str_starts_with($resolved, $root . DIRECTORY_SEPARATOR)) {
            return;
        }

        $extension = strtolower(pathinfo($resolved, PATHINFO_EXTENSION));
        if (!isset($contentTypes[$extension])) {
            return;
        }

        header('Content-Type: ' . $contentTypes[$extension]);
        header('Content-Length: ' . filesize($resolved));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: public, max-age=31536000, immutable');

        readfile($resolved);
        exit;
    }
}
