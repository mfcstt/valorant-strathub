<?php
require_once 'functions.php';
require_once 'database.php';
require_once 'src/models/Agent.php';

try {
    $database = new Database(config('database'));
    $agents = $database->query('SELECT * FROM agents WHERE name IN (?, ?) ORDER BY name', Agent::class, ['Cypher', 'Brimstone']);
    
    echo 'Agentes encontrados: ' . count($agents) . PHP_EOL;
    foreach ($agents as $agent) {
        echo "Agent: {$agent->name}, Photo: {$agent->photo}" . PHP_EOL;
    }
    
    if (count($agents) === 0) {
        echo 'Nenhum agente encontrado. Verificando todos os agentes...' . PHP_EOL;
        $allAgents = $database->query('SELECT * FROM agents ORDER BY name', Agent::class);
        echo 'Total de agentes no banco: ' . count($allAgents) . PHP_EOL;
        foreach ($allAgents as $agent) {
            echo "- {$agent->name} ({$agent->photo})" . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage() . PHP_EOL;
}