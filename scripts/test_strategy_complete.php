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

echo "=== Teste Completo de Criação de Estratégia ===\n\n";

// Criar arquivos de teste
echo "Criando arquivos de teste...\n";
$imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
file_put_contents(__DIR__ . '/test_image.jpg', $imageData);

$videoData = str_repeat('AAAAIGZ0eXBpc29tAAACAGlzb21pc28yYXZjMW1wNDEAAAAIZnJlZQAAACtmZGF0AAAA', 50);
file_put_contents(__DIR__ . '/test_video.mp4', base64_decode($videoData));

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
    'titulo' => 'Estratégia de Teste Completa',
    'categoria' => 'ataque',
    'descricao' => 'Esta é uma descrição de teste para a estratégia completa',
    'agente' => '1',
    'mapa' => '1'
];

// Simular dados FILES
$_FILES = [
    'capa' => [
        'name' => 'test_image.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => __DIR__ . '/test_image.jpg',
        'error' => UPLOAD_ERR_OK,
        'size' => filesize(__DIR__ . '/test_image.jpg')
    ],
    'video' => [
        'name' => 'test_video.mp4',
        'type' => 'video/mp4',
        'tmp_name' => __DIR__ . '/test_video.mp4',
        'error' => UPLOAD_ERR_OK,
        'size' => filesize(__DIR__ . '/test_video.mp4')
    ]
];

// Simular REQUEST_METHOD
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "Dados simulados:\n";
echo "POST: " . print_r($_POST, true) . "\n";
echo "FILES: " . print_r($_FILES, true) . "\n";

echo "Executando controller...\n";

// Capturar output do controller
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
$result = $database->query("SELECT COUNT(*) as count FROM estrategias WHERE title = 'Estratégia de Teste Completa'");
$count = $result->fetch(PDO::FETCH_ASSOC)['count'];

echo "Estratégias com título 'Estratégia de Teste Completa': " . $count . "\n";

// Limpar arquivos de teste
if (file_exists(__DIR__ . '/test_image.jpg')) {
    unlink(__DIR__ . '/test_image.jpg');
}
if (file_exists(__DIR__ . '/test_video.mp4')) {
    unlink(__DIR__ . '/test_video.mp4');
}

echo "\n=== Teste concluído ===\n";