<?php

require_once 'database.php';
require_once 'functions.php';

echo "🔧 Criando usuário de teste...\n\n";

try {
    $database = new Database(config('database'));
    
    $name = 'Usuário Teste';
    $email = 'teste@teste.com';
    $password = '12345678';
    
    // Verificar se usuário já existe
    $existing = $database->query(
        "SELECT * FROM users WHERE email = :email",
        null,
        compact('email')
    );
    
    if ($existing->fetch()) {
        echo "⚠️  Usuário já existe, deletando...\n";
        $database->query(
            "DELETE FROM users WHERE email = :email",
            null,
            compact('email')
        );
    }
    
    // Criar novo usuário
    $database->query(
        "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)",
        null,
        [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
        ]
    );
    
    echo "✅ Usuário criado com sucesso!\n";
    echo "   Email: $email\n";
    echo "   Senha: $password\n";
    echo "   Hash: " . password_hash($password, PASSWORD_BCRYPT) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
