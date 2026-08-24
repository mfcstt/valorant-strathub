<?php

declare(strict_types=1);

/**
 * Router para o servidor embutido do PHP (`php -S`).
 *
 * Uso:
 *   php -S localhost:8000 -t public public/dev-router.php
 *
 * O servidor embutido não tem regras de rewrite, então este arquivo faz o papel
 * do `vercel.json` em produção: entrega arquivos existentes e manda o resto para
 * o front controller com `?path=`.
 *
 * O atalho de login de desenvolvimento que existia aqui foi removido: ele
 * montava um cookie de autenticação a partir de um segredo padrão do código, o
 * que valia como bypass de login também em produção, já que este arquivo é o
 * `CMD` da imagem Docker.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$file = __DIR__ . $path;

// Arquivo real dentro de public/: deixa o servidor embutido entregar.
if ($path !== '/' && is_file($file)) {
    return false;
}

$_GET['path'] = ltrim($path, '/');

require __DIR__ . '/index.php';
