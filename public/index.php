<?php

require __DIR__ . "/../src/models/User.php";
require __DIR__ . "/../src/models/Estrategia.php";
require __DIR__ . "/../src/models/Agent.php";
require __DIR__ . "/../src/models/Map.php";
require __DIR__ . "/../src/models/Rating.php";

session_start();

require __DIR__ . "/../Flash.php";
require __DIR__ . "/../functions.php";
require __DIR__ . "/../Validation.php";
require __DIR__ . "/../Database.php";

// Criar instância global do banco de dados
$database = new Database(config('database'));

require __DIR__ . "/../routes.php";
