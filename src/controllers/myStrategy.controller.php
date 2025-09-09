<?php

unset($_SESSION["flash_formData"]);

if (!auth()) {
    abort(403, 'Você precisa estar logado para acessar essa página.');
}

$search = $_REQUEST['pesquisar'] ?? '';

$estrategias = $search ? Estrategia::all($search) : Estrategia::myEstrategias(auth()->id);

view("app", compact('estrategias', 'search'), "myStrategy");
