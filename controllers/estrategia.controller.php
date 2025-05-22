<?php
require "./dados.php";
$id = $_REQUEST['id'];

$filtrado = array_filter($estrategias, fn($e) => $e['id'] == $id);
$estrategia = array_pop($filtrado);

$view = "estrategia";
require "views/template/app.php";
?>
