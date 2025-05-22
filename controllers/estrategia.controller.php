<?php

$id = $_REQUEST['id'];
$db = new DB;

$estrategia = $db->estrategia($id);

require "./database.php";

$view = "estrategia";
require "views/template/app.php";
?>
