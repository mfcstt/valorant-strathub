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

echo "=== Teste: Estratégia com apenas Imagem ===\n\n";

// Criar arquivo de imagem de teste
$imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
file_put_contents(__DIR__ . '/test_image_only.jpg', $imageData);

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
    'titulo' => 'Estratégia Apenas Imagem',
    'categoria' => 'defesa',
    'descricao' => 'Descrição de teste apenas com imagem',
    'agente' => '1',
    'mapa' => '1'
];

// Simular apenas imagem
$_FILES = [
    'capa' => [
        'name' => 'test_image_only.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => __DIR__ . '/test_image_only.jpg',
        'error' => UPLOAD_ERR_OK,
        'size' => filesize(__DIR__ . '/test_image_only.jpg')
    ]
];

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

// Verificar se a estratégia foi criada
$database = new Database(config('database')['database']);
$result = $database->query("SELECT COUNT(*) as count FROM estrategias WHERE title = 'Estratégia Apenas Imagem'");
$count = $result->fetch(PDO::FETCH_ASSOC)['count'];

echo "Estratégias com título 'Estratégia Apenas Imagem': " . $count . "\n";

// Limpar arquivos de teste
if (file_exists(__DIR__ . '/test_image_only.jpg')) {
    unlink(__DIR__ . '/test_image_only.jpg');
}

echo "\n=== Teste concluído ===\n";