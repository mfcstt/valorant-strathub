<?php

declare(strict_types=1);

/**
 * Bootstrap da suíte de testes.
 *
 * Os testes não sobem um servidor HTTP: eles exercitam as classes diretamente,
 * com uma sessão simulada em array e um banco SQLite temporário por caso de
 * teste. É o que permite rodar a suíte sem infraestrutura nenhuma.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

// A aplicação usa $_SESSION diretamente. Fora do contexto web não existe sessão
// real, então um array simples cumpre o contrato — e ainda deixa cada teste
// partir de um estado limpo.
if (!isset($_SESSION)) {
    $_SESSION = [];
}

$_SERVER['REQUEST_METHOD'] ??= 'GET';
$_SERVER['REQUEST_URI'] ??= '/';
$_SERVER['HTTP_HOST'] ??= 'localhost';
