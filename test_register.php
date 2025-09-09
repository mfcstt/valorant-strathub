<?php

require_once 'database.php';
require_once 'functions.php';
require_once 'Validation.php';
require_once 'Flash.php';

echo "🔧 Testando registro...\n\n";

try {
    $name = 'Novo Usuário';
    $email = 'novo@teste.com';
    $password = '12345678@';
    
    echo "📝 Testando registro com:\n";
    echo "   Nome: $name\n";
    echo "   Email: $email\n";
    echo "   Senha: $password\n\n";
    
    $validation = Validation::validate([
        'nome' => ['required'],
        'email' => ['required', 'email', 'unique:users'],
        'senha' => ['required', 'min:8', 'max:30', 'strong']
    ], [
        'nome' => $name,
        'email' => $email,
        'senha' => $password
    ]);
    
    if ($validation->notPassed('register')) {
        echo "❌ Validação falhou!\n";
        var_dump($validation->validations);
    } else {
        echo "✅ Validação passou!\n";
        
        $database = new Database(config('database'));
        
        $database->query(
            "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)",
            null,
            [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
            ]
        );
        
        echo "✅ Usuário registrado com sucesso!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
