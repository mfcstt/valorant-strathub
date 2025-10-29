<?php

// Logout: limpa sessão e cookies de autenticação e redireciona
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['auth']);
unset($_SESSION['guest']);

$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

// Expirar cookies
setcookie('auth_uid', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
setcookie('auth_sig', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

flash()->put('message', 'Você saiu da sua conta.');
header('Location: /login');
exit();
