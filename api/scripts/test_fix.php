<?php
// Simular exatamente o que o controller strategy-create faz
$_SERVER['REQUEST_METHOD'] = 'GET';

// Incluir os mesmos arquivos que o controller
require_once '../functions.php';
require_once '../database.php';
require_once '../src/models/Agent.php';
require_once '../src/models/Map.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== SIMULANDO CONTROLLER STRATEGY-CREATE ===" . PHP_EOL;

try {
    // Correção temporária para Cypher e Brimstone (igual ao controller)
    $database = new Database(config('database'));
    
    // Verificar se precisa corrigir
    $needsFix = $database->query("SELECT COUNT(*) as count FROM agents WHERE (name = 'Brimstone' AND photo = 'brim.png') OR (name = 'Cypher' AND photo = 'chypher.png')", null, []);
    
    echo "Verificando se precisa corrigir..." . PHP_EOL;
    echo "Registros que precisam de correção: " . ($needsFix ? $needsFix[0]['count'] : 0) . PHP_EOL;
    
    if ($needsFix && $needsFix[0]['count'] > 0) {
        echo "Aplicando correções..." . PHP_EOL;
        
        // Corrigir Brimstone
        $result1 = $database->query("UPDATE agents SET photo = ? WHERE name = ?", null, ['brimstone.png', 'Brimstone']);
        echo "- Brimstone corrigido" . PHP_EOL;
        
        // Corrigir Cypher
        $result2 = $database->query("UPDATE agents SET photo = ? WHERE name = ?", null, ['cypher.png', 'Cypher']);
        echo "- Cypher corrigido" . PHP_EOL;
    } else {
        echo "Nenhuma correção necessária." . PHP_EOL;
    }
    
    // Carregar agentes (igual ao controller)
    $agents = Agent::all();
    
    echo PHP_EOL . "=== VERIFICANDO RESULTADO ===" . PHP_EOL;
    echo "Total de agentes: " . count($agents) . PHP_EOL;
    
    foreach ($agents as $agent) {
        if (strtolower($agent->name) === 'cypher' || strtolower($agent->name) === 'brimstone') {
            echo "✓ {$agent->name}: {$agent->photo}" . PHP_EOL;
        }
    }
    
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage() . PHP_EOL;
    echo 'Stack trace: ' . $e->getTraceAsString() . PHP_EOL;
}