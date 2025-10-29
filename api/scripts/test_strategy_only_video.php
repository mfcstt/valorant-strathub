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

echo "=== Teste: Estratégia com apenas Vídeo ===\n\n";

// Criar arquivo de vídeo de teste
$videoData = str_repeat('AAAAIGZ0eXBpc29tAAACAGlzb21pc28yYXZjMW1wNDEAAAAIZnJlZQAAACtmZGF0AAAA', 50);
file_put_contents(__DIR__ . '/test_video_only.mp4', base64_decode($videoData));

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
    'titulo' => 'Estratégia Apenas Vídeo',
    'categoria' => 'ataque',
    'descricao' => 'Descrição de teste apenas com vídeo',
    'agente' => '1',
    'mapa' => '1'
];

// Simular apenas vídeo
$_FILES = [
    'video' => [
        'name' => 'test_video_only.mp4',
        'type' => 'video/mp4',
        'tmp_name' => __DIR__ . '/test_video_only.mp4',
        'error' => UPLOAD_ERR_OK,
        'size' => filesize(__DIR__ . '/test_video_only.mp4')
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
$result = $database->query("SELECT COUNT(*) as count FROM estrategias WHERE title = 'Estratégia Apenas Vídeo'");
$count = $result->fetch(PDO::FETCH_ASSOC)['count'];

echo "Estratégias com título 'Estratégia Apenas Vídeo': " . $count . "\n";

// Limpar arquivos de teste
if (file_exists(__DIR__ . '/test_video_only.mp4')) {
    unlink(__DIR__ . '/test_video_only.mp4');
}

echo "\n=== Teste concluído ===\n";