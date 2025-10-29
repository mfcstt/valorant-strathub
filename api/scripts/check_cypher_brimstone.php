<?php
header('Content-Type: text/plain; charset=utf-8');

// Simular uma requisição para a página strategy-create
$_SERVER['REQUEST_METHOD'] = 'GET';

// Incluir os arquivos necessários
require_once '../functions.php';
require_once '../database.php';
require_once '../src/models/Agent.php';
require_once '../src/models/Map.php';

echo "=== VERIFICANDO AGENTES NA PÁGINA STRATEGY-CREATE ===" . PHP_EOL;

try {
    // Aplicar a correção primeiro
    $database = new Database(config('database'));
    
    // Verificar se precisa corrigir
    $needsFix = $database->query("SELECT COUNT(*) as count FROM agents WHERE (name = 'Brimstone' AND photo = 'brim.png') OR (name = 'Cypher' AND photo = 'chypher.png')", null, []);
    
    if ($needsFix && $needsFix[0]['count'] > 0) {
        echo "Aplicando correções..." . PHP_EOL;
        
        // Corrigir Brimstone
        $database->query("UPDATE agents SET photo = ? WHERE name = ?", null, ['brimstone.png', 'Brimstone']);
        echo "- Brimstone corrigido" . PHP_EOL;
        
        // Corrigir Cypher
        $database->query("UPDATE agents SET photo = ? WHERE name = ?", null, ['cypher.png', 'Cypher']);
        echo "- Cypher corrigido" . PHP_EOL;
    } else {
        echo "Nenhuma correção necessária." . PHP_EOL;
    }
    
    // Simular o que o controller faz
    $agents = Agent::all();
    $maps = Map::all();
    
    echo "Total de agentes carregados: " . count($agents) . PHP_EOL;
    echo "Total de mapas carregados: " . count($maps) . PHP_EOL;
    
    echo PHP_EOL . "=== PROCURANDO CYPHER E BRIMSTONE ===" . PHP_EOL;
    
    $cypherFound = false;
    $brimstoneFound = false;
    
    foreach ($agents as $agent) {
        if (strtolower($agent->name) === 'cypher') {
            $cypherFound = true;
            echo "✓ CYPHER encontrado: {$agent->name} - Foto: {$agent->photo}" . PHP_EOL;
        }
        if (strtolower($agent->name) === 'brimstone') {
            $brimstoneFound = true;
            echo "✓ BRIMSTONE encontrado: {$agent->name} - Foto: {$agent->photo}" . PHP_EOL;
        }
    }
    
    if (!$cypherFound) {
        echo "✗ CYPHER NÃO encontrado" . PHP_EOL;
    }
    if (!$brimstoneFound) {
        echo "✗ BRIMSTONE NÃO encontrado" . PHP_EOL;
    }
    
    echo PHP_EOL . "=== TODOS OS AGENTES ===" . PHP_EOL;
    foreach ($agents as $agent) {
        echo "- {$agent->name} ({$agent->photo})" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage() . PHP_EOL;
    echo 'Stack trace: ' . $e->getTraceAsString() . PHP_EOL;
}