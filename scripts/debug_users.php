<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';

echo "=== Debug Usuários ===\n\n";

$database = new Database(config('database')['database']);

try {
    // Verificar se a tabela existe (PostgreSQL)
    $tables = $database->query("SELECT table_name FROM information_schema.tables WHERE table_name = 'users'");
    
    if (!$tables || empty($tables)) {
        echo "Tabela 'users' não encontrada!\n";
        exit(1);
    }
    
    echo "Tabela 'users' existe.\n\n";
    
    // Contar usuários
    $countStmt = $database->query("SELECT COUNT(*) as total FROM users");
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de usuários: " . ($countResult['total'] ?? 'erro') . "\n\n";
    
    // Listar usuários
    $usersStmt = $database->query("SELECT * FROM users LIMIT 5");
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($users && is_array($users)) {
        echo "Primeiros 5 usuários:\n";
        foreach ($users as $user) {
            print_r($user);
            echo "\n";
        }
    } else {
        echo "Nenhum usuário encontrado.\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}