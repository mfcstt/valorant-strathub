<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== DEBUG ARQUIVO .ENV ===" . PHP_EOL;
$envPath = __DIR__ . '/../.env';
echo "Caminho do .env: " . $envPath . PHP_EOL;
echo "Arquivo .env existe: " . (file_exists($envPath) ? 'SIM' : 'NÃO') . PHP_EOL;

if (file_exists($envPath)) {
    echo "Conteúdo do .env:" . PHP_EOL;
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') !== 0) {
            echo "  " . $line . PHP_EOL;
        }
    }
}

echo PHP_EOL . "=== CARREGANDO CONFIGURAÇÃO ===" . PHP_EOL;
require_once '../functions.php';

$config = config('database');
echo "Driver: " . ($config['driver'] ?? 'não definido') . PHP_EOL;
echo "Host: " . ($config['host'] ?? 'não definido') . PHP_EOL;
echo "Port: " . ($config['port'] ?? 'não definido') . PHP_EOL;
echo "DB Name: " . ($config['dbname'] ?? 'não definido') . PHP_EOL;
echo "User: " . ($config['user'] ?? 'não definido') . PHP_EOL;
echo "Password: " . (isset($config['password']) ? '[DEFINIDO]' : 'não definido') . PHP_EOL;

echo PHP_EOL . "=== TESTANDO CONEXÃO ===" . PHP_EOL;

try {
    require_once '../database.php';
    require_once '../src/models/Agent.php';
    
    $database = new Database($config);
    echo "Conexão estabelecida com sucesso!" . PHP_EOL;
    
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