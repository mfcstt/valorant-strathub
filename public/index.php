<?php

// Ativa exibição de erros para debug (opcional, desligar em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir arquivos necessários
require __DIR__ . "/../src/models/User.php";
require __DIR__ . "/../src/models/Estrategia.php";
require __DIR__ . "/../src/models/Agent.php";
require __DIR__ . "/../src/models/Map.php";
require __DIR__ . "/../src/models/Rating.php";

session_start();

require __DIR__ . "/../Flash.php";
require __DIR__ . "/../functions.php";
require __DIR__ . "/../Validation.php";
require __DIR__ . "/../database.php";

// Configurações do banco via variáveis de ambiente
$config = [
    'driver' => 'pgsql',
    'host' => getenv('DB_HOST'),
    'port' => getenv('DB_PORT') ?: 5432,
    'dbname' => getenv('DB_NAME'),
    'user' => getenv('DB_USER'),
    'password' => getenv('DB_PASS'),
    'sslmode' => 'require'
];

// Criar instância global do banco de dados
$database = new Database($config);

// Inclui as rotas
require __DIR__ . "/../routes.php";

