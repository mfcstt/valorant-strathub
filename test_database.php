<?php

require_once 'database.php';
require_once 'functions.php';

echo "🔍 Testando banco de dados...\n\n";

try {
    $database = new Database(config('database'));
    
    // Testar tabelas
    $tables = $database->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Tabelas encontradas: " . implode(', ', $tables) . "\n\n";
    
    // Contar agentes
    $agentResult = $database->query("SELECT COUNT(*) as total FROM agents")->fetch(PDO::FETCH_ASSOC);
    echo "👥 Total de agentes: " . $agentResult['total'] . "\n";
    
    // Listar alguns agentes
    $agents = $database->query("SELECT name, photo FROM agents LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo "🎯 Primeiros agentes:\n";
    foreach ($agents as $agent) {
        echo "  - " . $agent['name'] . " (" . $agent['photo'] . ")\n";
    }
    
    // Contar usuários
    $userResult = $database->query("SELECT COUNT(*) as total FROM users")->fetch(PDO::FETCH_ASSOC);
    echo "\n👤 Total de usuários: " . $userResult['total'] . "\n";
    
    // Contar estratégias
    $estrategiaResult = $database->query("SELECT COUNT(*) as total FROM estrategias")->fetch(PDO::FETCH_ASSOC);
    echo "📝 Total de estratégias: " . $estrategiaResult['total'] . "\n";
    
    echo "\n✅ Banco de dados funcionando perfeitamente!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
