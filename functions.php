<?php
// Inicia sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function view($view, $data = [], $component = null) {
    foreach ($data as $key => $value) {
        $$key = $value;
    }

    require "../src/views/templates/index.template.php";
}

function input($type, $name, $placeholder, $classIcon, $form = '') { 
    require "../src/views/partials/_input.php";
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
    return $_SESSION['auth'] ?? null;
}

function config($key = null)
{
    $config = require 'config.php';
    if ($key && isset($config[$key])) {
        return $config[$key];
    }
    return $config;
}

function printLog($text, $var) {
    echo '<pre class="text-red-500">';
    echo $text;
    print_r($var);
    echo '</pre>';
}
