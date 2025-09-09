<?php

session_start();
require_once 'database.php';
require_once 'functions.php';
require_once 'src/models/User.php';

echo "🔍 Testando sessão e autenticação...\n\n";

// Simular login
$email = 'teste@teste.com';
$password = '12345678';

echo "📧 Tentando fazer login com: $email\n";

$database = new Database(config('database'));

$results = $database->query(
    "SELECT * FROM users WHERE email = :email",
    User::class,
    compact('email')
);

$user = is_array($results) ? ($results[0] ?? null) : $results;

if ($user && password_verify($password, $user->password)) {
    echo "✅ Login bem-sucedido!\n";
    echo "   Nome: " . $user->name . "\n";
    echo "   Email: " . $user->email . "\n";
    
    // Armazenar na sessão
    $_SESSION['auth'] = $user;
    echo "✅ Usuário armazenado na sessão\n";
    
    // Testar função auth()
    $authUser = auth();
    if ($authUser) {
        echo "✅ Função auth() retornou usuário:\n";
        echo "   Nome: " . $authUser->name . "\n";
        echo "   Email: " . $authUser->email . "\n";
    } else {
        echo "❌ Função auth() retornou null\n";
    }
    
    // Verificar sessão diretamente
    echo "🔍 Sessão atual:\n";
    echo "   Session ID: " . session_id() . "\n";
    echo "   Auth na sessão: " . (isset($_SESSION['auth']) ? 'SIM' : 'NÃO') . "\n";
    
} else {
    echo "❌ Login falhou!\n";
}
