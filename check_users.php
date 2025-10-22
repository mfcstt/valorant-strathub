<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';

echo "=== Verificação de Usuários ===\n\n";

$database = new Database(config('database')['database']);

// Verificar usuários existentes
$users = $database->query("SELECT id, name, email, created_at FROM users ORDER BY id");

echo "Usuários existentes:\n";
if ($users && is_array($users)) {
    foreach ($users as $user) {
        if (is_array($user)) {
            echo "- ID: {$user['id']}, Nome: {$user['name']}, Email: {$user['email']}\n";
        } else {
            echo "- ID: {$user->id}, Nome: {$user->name}, Email: {$user->email}\n";
        }
    }
} else {
    echo "Nenhum usuário encontrado.\n";
}

echo "\n";

// Criar usuário de teste se não existir
$testEmail = 'teste@valorant.com';
$existingUser = $database->query("SELECT id FROM users WHERE email = :email", null, ['email' => $testEmail]);

if (!$existingUser) {
    echo "Criando usuário de teste...\n";
    
    $hashedPassword = password_hash('123456', PASSWORD_DEFAULT);
    
    $database->query(
        "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)",
        null,
        [
            'name' => 'Usuário Teste',
            'email' => $testEmail,
            'password' => $hashedPassword
        ]
    );
    
    echo "✓ Usuário de teste criado:\n";
    echo "  Email: {$testEmail}\n";
    echo "  Senha: 123456\n";
} else {
    echo "✓ Usuário de teste já existe:\n";
    echo "  Email: {$testEmail}\n";
    echo "  Senha: 123456\n";
}

echo "\n=== Verificação concluída ===\n";