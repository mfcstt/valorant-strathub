<?php
require_once '../functions.php';
require_once '../database.php';

try {
    $database = new Database(config('database'));
    
    echo "=== CORRIGINDO NOMES DOS ARQUIVOS DE AGENTES ===" . PHP_EOL;
    
    // Corrigir Brimstone: de 'brim.png' para 'brimstone.png'
    $result1 = $database->query("UPDATE agents SET photo = ? WHERE name = ?", null, ['brimstone.png', 'Brimstone']);
    echo "✓ Brimstone atualizado: brim.png → brimstone.png" . PHP_EOL;
    
    // Corrigir Cypher: de 'chypher.png' para 'cypher.png'
    $result2 = $database->query("UPDATE agents SET photo = ? WHERE name = ?", null, ['cypher.png', 'Cypher']);
    echo "✓ Cypher atualizado: chypher.png → cypher.png" . PHP_EOL;
    
    echo PHP_EOL . "=== VERIFICANDO CORREÇÕES ===" . PHP_EOL;
    
    // Verificar se as correções foram aplicadas
    $agents = $database->query("SELECT * FROM agents WHERE name IN (?, ?) ORDER BY name", null, ['Cypher', 'Brimstone']);
    
    foreach ($agents as $agent) {
        echo "- {$agent['name']}: {$agent['photo']}" . PHP_EOL;
    }
    
    echo PHP_EOL . "Correções aplicadas com sucesso!" . PHP_EOL;
    
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage() . PHP_EOL;
}