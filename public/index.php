<?php 

require "../src/models/User.php";
require "../src/models/Estrategia.php";
require "../src/models/Agent.php";
require "../src/models/Map.php";
require "../src/models/Rating.php";

session_start();

require "../Flash.php";
require "../functions.php";
require "../Validation.php";
require "../Database.php";

// Criar instância global do banco de dados
$database = new Database(config('database')['database']);

require "../routes.php";