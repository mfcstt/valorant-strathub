<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';
require_once 'Flash.php';
require_once 'Validation.php';
require_once 'src/models/Video.php';
require_once 'src/models/Image.php';
require_once 'src/models/Agent.php';
require_once 'src/models/Map.php';
require_once 'src/services/SupabaseStorageService.php';

echo "=== Teste: Estratégia sem Imagem e sem Vídeo ===\n\n";

// Simular sessão de usuário logado
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$userSession = new stdClass();
$userSession->id = 1;
$userSession->name = 'Test User';
$userSession->email = 'test@test.com';
$_SESSION['auth'] = $userSession;

// Simular dados POST
$_POST = [
    'titulo' => 'Estratégia Sem Arquivos',
    'categoria' => 'retake',
    'descricao' => 'Descrição de teste sem arquivos',
    'agente' => '1',
    'mapa' => '1'
];

// Sem arquivos
$_FILES = [];

// Simular REQUEST_METHOD
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "Executando controller...\n";

ob_start();
try {
    include 'src/controllers/strategy-create.controller.php';
} catch (Exception $e) {
    echo "Erro no controller: " . $e->getMessage() . "\n";
}
$output = ob_get_clean();

echo "Output do controller: " . $output . "\n";

// Verificar se a estratégia NÃO foi criada
$database = new Database(config('database')['database']);
$result = $database->query("SELECT COUNT(*) as count FROM estrategias WHERE title = 'Estratégia Sem Arquivos'");
$count = $result->fetch(PDO::FETCH_ASSOC)['count'];

echo "Estratégias com título 'Estratégia Sem Arquivos': " . $count . "\n";

if ((int)$count === 0) {
    echo "✓ Nenhuma estratégia criada, validação funcionou.\n";
} else {
    echo "✗ Estratégia foi criada indevidamente.\n";
}

echo "\n=== Teste concluído ===\n";