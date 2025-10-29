<?php
// Inicia sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function view($view, $data = [], $component = null) {
    foreach ($data as $key => $value) {
        $$key = $value;
    }

    require __DIR__ . "/src/views/templates/index.template.php";
}

function input($type, $name, $placeholder, $classIcon, $form = '') { 
    require __DIR__ . "/src/views/partials/_input.php";
}

function abort($code, $message)
{
    http_response_code($code);
    view("httpErrors", compact('code', 'message'));
    die();
}

function flash()
{
    return new Flash;
}

function auth() {
    // Se já existe na sessão, retorna
    if (isset($_SESSION['auth'])) {
        return $_SESSION['auth'];
    }

    // Fallback: reidrata a sessão a partir de cookies assinados (compatível com ambientes serverless)
    $uid = $_COOKIE['auth_uid'] ?? null;
    $sig = $_COOKIE['auth_sig'] ?? null;

    if ($uid && $sig) {
        $secret = $_ENV['APP_SECRET'] ?? 'strathub-fallback-secret';
        $expected = hash_hmac('sha256', (string)$uid, $secret);

        if (hash_equals($expected, $sig)) {
            // Buscar usuário e reidratar sessão
            if (class_exists('User')) {
                $user = User::get($uid);
                if ($user) {
                    $userSession = new stdClass();
                    $userSession->id = $user->id;
                    $userSession->name = $user->name;
                    $userSession->email = $user->email;
                    $userSession->avatar = $user->avatar;
                    $userSession->elo = $user->elo ?? 'ferro';
                    $userSession->created_at = $user->created_at;
                    $userSession->updated_at = $user->updated_at;
                    $_SESSION['auth'] = $userSession;
                    return $_SESSION['auth'];
                }
            }
        }
    }

    return null;
}

function config($key = null)
{
    $config = require 'config.php';
    if ($key && isset($config[$key])) {
        return $config[$key];
    }
    return $config;
}
