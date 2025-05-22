<?php

$db = new DB();

$pesquisar = $_REQUEST['pesquisar'] ?? '';

$estrategias = $db->estrategias($pesquisar);

$view = "index";
require "views/template/app.php";

?>