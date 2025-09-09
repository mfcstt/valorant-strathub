<?php

require_once 'database.php';
require_once 'functions.php';

echo "🔍 Testando funcionalidades do projeto...\n\n";

try {
    $database = new Database(config('database'));
    
    // Teste 1: Verificar tabelas
    echo "📋 Teste 1: Verificando tabelas...\n";
    $tables = $database->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tableNames = [];
    foreach ($tables as $table) {
        $tableNames[] = $table['table_name'];
    }
    echo "✅ Tabelas encontradas: " . implode(', ', $tableNames) . "\n\n";
    
    // Teste 2: Verificar agentes
    echo "🎯 Teste 2: Verificando agentes...\n";
    $agents = $database->query("SELECT COUNT(*) as total FROM agents");
    $agentCount = $agents->fetch(PDO::FETCH_ASSOC);
    echo "✅ Total de agentes: " . $agentCount['total'] . "\n\n";
    
    // Teste 3: Verificar usuários
    echo "👤 Teste 3: Verificando usuários...\n";
    $users = $database->query("SELECT COUNT(*) as total FROM users");
    $userCount = $users->fetch(PDO::FETCH_ASSOC);
    echo "✅ Total de usuários: " . $userCount['total'] . "\n\n";
    
    // Teste 4: Verificar estratégias
    echo "📝 Teste 4: Verificando estratégias...\n";
    $estrategias = $database->query("SELECT COUNT(*) as total FROM estrategias");
    $estrategiaCount = $estrategias->fetch(PDO::FETCH_ASSOC);
    echo "✅ Total de estratégias: " . $estrategiaCount['total'] . "\n\n";
    
    // Teste 5: Testar query complexa do modelo Estrategia
    echo "🔍 Teste 5: Testando query complexa...\n";
    $query = "SELECT 
                e.id, e.title, e.category, e.description, e.cover, e.user_id, e.agent_id, e.created_at, e.updated_at,
                a.name AS agent_name, a.photo AS agent_photo,
                COALESCE(SUM(r.rating)/COUNT(r.id), 0) AS rating_average,
                COALESCE(COUNT(r.id), 0) AS ratings_count
            FROM estrategias e
            LEFT JOIN agents a ON a.id = e.agent_id
            LEFT JOIN ratings r ON r.estrategia_id = e.id
            WHERE e.id = 1
            GROUP BY e.id, a.name, a.photo";
    
    $result = $database->query($query);
    $estrategia = $result->fetch(PDO::FETCH_ASSOC);
    if ($estrategia) {
        echo "✅ Query complexa funcionando!\n";
        echo "   Estratégia: " . ($estrategia['title'] ?? 'N/A') . "\n";
        echo "   Agente: " . ($estrategia['agent_name'] ?? 'N/A') . "\n";
    } else {
        echo "⚠️  Query complexa retornou vazio (pode ser normal se não há estratégias)\n";
    }
    
    echo "\n🎉 Todos os testes básicos passaram!\n";
    echo "📊 O banco de dados está funcionando corretamente.\n";
    
} catch (Exception $e) {
    echo "❌ Erro nos testes: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
