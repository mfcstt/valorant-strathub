<?php
// Router de desenvolvimento para o servidor embutido do PHP.
// Serve arquivos estáticos diretamente; caso contrário, envia para o front controller.

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
$qs = [];
if ($query) parse_str($query, $qs);
$file = __DIR__ . $path;

// Se for um arquivo existente dentro de public, deixa o servidor servir normalmente
if ($path !== '/' && file_exists($file) && is_file($file)) {
    return false;
}

// Prepara a query '?path=' para o front controller, como em produção (Vercel)
$_GET['path'] = ltrim($path, '/');

// Hook opcional de debug: permite simular login com ?debugAuth=1
if (isset($qs['debugAuth']) && $qs['debugAuth'] == '1') {
    $secret = $_ENV['APP_SECRET'] ?? 'strathub-fallback-secret';
    $uid = $qs['uid'] ?? '1';
    $sig = hash_hmac('sha256', (string)$uid, $secret);
    $isSecure = false; // dev local
    setcookie('auth_uid', (string)$uid, [
        'expires' => time() + 3600,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    setcookie('auth_sig', $sig, [
        'expires' => time() + 3600,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
require __DIR__ . '/index.php';