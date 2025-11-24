<?php

// Em Vercel usamos rewrite para enviar '?path=...'. Localmente continua funcionando com REQUEST_URI
$rawPath = $_GET['path'] ?? (parse_url($_SERVER['REQUEST_URI'])['path'] ?? '');
$controller = trim($rawPath, '/');

if ($controller === '') $controller = 'login';

if (!file_exists(__DIR__ . "/src/controllers/{$controller}.controller.php")) {
  abort(404, 'Página não encontrada.');
}

require __DIR__ . "/src/controllers/{$controller}.controller.php";