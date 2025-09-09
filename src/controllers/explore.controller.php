<?php

unset($_SESSION["flash_validations_login"]);

if (!auth()) {
    abort(403, 'Você precisa estar logado para acessar essa página.');
}

$message = $_REQUEST['message'] ?? '';
$search = $_REQUEST['pesquisar'] ?? '';

$estrategias = $search ? Estrategia::all($search) : Estrategia::all();

view('app', compact('message', 'estrategias', 'search'), 'explore');
