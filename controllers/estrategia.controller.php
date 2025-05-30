<?php
require '../database.php';
require '../models/Estrategia.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die('ID da estratégia não especificado.');
}

$db = new DB();

$estrategia = $db->estrategia($id);

if (!$estrategia) {
    die('Estratégia não encontrada.');
}

require '../views/estrategia.view.php';
