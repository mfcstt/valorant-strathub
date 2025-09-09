<?php

require_once 'database.php';
require_once 'functions.php';
require_once 'src/models/User.php';

echo "🔍 Testando login...\n\n";

try {
    $database = new Database(config('database'));
    
    // Buscar usuário
    $email = 'teste@teste.com';
    $password = '12345678'; // senha de teste
    
    echo "📧 Testando login com: $email\n";
    
    $results = $database->query(
        "SELECT * FROM users WHERE email = :email",
        User::class,
        compact('email')
    );
    
    echo "🔍 Resultado da query: ";
    var_dump($results);
    
    $user = is_array($results) ? ($results[0] ?? null) : $results;
    
    if ($user) {
        echo "✅ Usuário encontrado!\n";
        echo "   Nome: " . $user->name . "\n";
        echo "   Email: " . $user->email . "\n";
        echo "   Hash da senha: " . substr($user->password, 0, 20) . "...\n";
        
        // Testar verificação de senha
        if (password_verify($password, $user->password)) {
            echo "✅ Senha correta!\n";
        } else {
            echo "❌ Senha incorreta!\n";
        }
    } else {
        echo "❌ Usuário não encontrado!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
