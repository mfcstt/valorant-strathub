<?php

session_start();
require_once 'database.php';
require_once 'functions.php';
require_once 'src/models/User.php';

echo "🔍 Testando login final...\n\n";

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
    
    // Criar objeto User limpo para a sessão (sem PDO)
    $userSession = new stdClass();
    $userSession->id = $user->id;
    $userSession->name = $user->name;
    $userSession->email = $user->email;
    $userSession->avatar = $user->avatar;
    $userSession->created_at = $user->created_at;
    $userSession->updated_at = $user->updated_at;
    
    $_SESSION['auth'] = $userSession;
    echo "✅ Usuário armazenado na sessão (sem PDO)\n";
    
    // Testar função auth()
    $authUser = auth();
    if ($authUser) {
        echo "✅ Função auth() retornou usuário:\n";
        echo "   ID: " . $authUser->id . "\n";
        echo "   Nome: " . $authUser->name . "\n";
        echo "   Email: " . $authUser->email . "\n";
        echo "   Avatar: " . $authUser->avatar . "\n";
    } else {
        echo "❌ Função auth() retornou null\n";
    }
    
    // Verificar se pode acessar explore
    if (auth()) {
        echo "✅ Usuário pode acessar /explore\n";
    } else {
        echo "❌ Usuário NÃO pode acessar /explore\n";
    }
    
} else {
    echo "❌ Login falhou!\n";
}
