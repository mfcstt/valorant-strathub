<?php

unset($_SESSION["flash_formData"]);

// Verificação se o usuário está logado!
if (!auth()) {
  abort(403, 'Você precisa estar logado para acessar essa página.');
}

$estrategias = Estrategia::myEstrategias(auth()->id);

$search = $_REQUEST['pesquisar'] ?? '';

if ($search != '') {
  // Armazenar valores do form na SESSION.
  flash()->put('formData', $_POST);

  $estrategias = Estrategia::all($search);
}

view("app", compact('estrategias', 'search'), "myStrategy");
