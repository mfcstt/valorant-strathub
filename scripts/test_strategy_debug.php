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

echo "=== Debug de Criação de Estratégia ===\n\n";

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
$_SESSION['user'] = ['id' => 1, 'name' => 'Test User', 'email' => 'test@test.com'];

// Dados para teste
$user_id = 1;
$title = 'Estratégia Debug Test';
$category = 'ataque';
$agent_id = '1';
$description = 'Esta é uma descrição de teste para debug';
$map_id = '1';

$fileCover = [
    'name' => 'test_image.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => __DIR__ . '/test_image.jpg',
    'error' => UPLOAD_ERR_OK,
    'size' => filesize(__DIR__ . '/test_image.jpg')
];

$fileVideo = [
    'name' => 'test_video.mp4',
    'type' => 'video/mp4',
    'tmp_name' => __DIR__ . '/test_video.mp4',
    'error' => UPLOAD_ERR_OK,
    'size' => filesize(__DIR__ . '/test_video.mp4')
];

echo "Dados preparados:\n";
echo "User ID: $user_id\n";
echo "Title: $title\n";
echo "Category: $category\n";
echo "Agent ID: $agent_id\n";
echo "Map ID: $map_id\n";
echo "Cover file size: " . $fileCover['size'] . " bytes\n";
echo "Video file size: " . $fileVideo['size'] . " bytes\n\n";

// Testar upload da imagem
echo "Testando upload da imagem...\n";
$cover_image_id = null;
try {
    $storageService = new SupabaseStorageService();
    $uploadedImage = $storageService->uploadImage($fileCover, $user_id);
    
    if ($uploadedImage) {
        $cover_image_id = $uploadedImage->id;
        echo "✓ Imagem uploaded com sucesso. ID: $cover_image_id\n";
    } else {
        echo "✗ Falha no upload da imagem\n";
    }
} catch (Exception $e) {
    echo "✗ Erro no upload da imagem: " . $e->getMessage() . "\n";
}

// Testar upload do vídeo
echo "Testando upload do vídeo...\n";
$video_id = null;
try {
    $storageService = new SupabaseStorageService();
    $uploadedVideo = $storageService->uploadVideo($fileVideo, $user_id);
    
    if ($uploadedVideo) {
        $video_id = $uploadedVideo->id;
        echo "✓ Vídeo uploaded com sucesso. ID: $video_id\n";
    } else {
        echo "✗ Falha no upload do vídeo\n";
    }
} catch (Exception $e) {
    echo "✗ Erro no upload do vídeo: " . $e->getMessage() . "\n";
}

// Testar inserção no banco
echo "\nTestando inserção no banco de dados...\n";
try {
    $database = new Database(config('database')['database']);
    
    echo "Parâmetros para inserção:\n";
    echo "title: $title\n";
    echo "category: $category\n";
    echo "description: $description\n";
    echo "cover_image_id: $cover_image_id\n";
    echo "video_id: $video_id\n";
    echo "user_id: $user_id\n";
    echo "agent_id: $agent_id\n";
    echo "map_id: $map_id\n\n";

    $result = $database->query(
        "INSERT INTO estrategias (title, category, description, cover_image_id, video_id, user_id, agent_id, map_id) 
        VALUES (:title, :category, :description, :cover_image_id, :video_id, :user_id, :agent_id, :map_id)",
        null,
        compact('title', 'category', 'description', 'cover_image_id', 'video_id', 'user_id', 'agent_id', 'map_id')
    );
    
    echo "✓ Estratégia inserida com sucesso no banco de dados\n";
    
    // Verificar se foi inserida
    $checkResult = $database->query("SELECT * FROM estrategias WHERE title = :title ORDER BY created_at DESC LIMIT 1", null, ['title' => $title]);
    $strategy = $checkResult->fetch(PDO::FETCH_ASSOC);
    
    if ($strategy) {
        echo "✓ Estratégia encontrada no banco:\n";
        echo "  ID: " . $strategy['id'] . "\n";
        echo "  Título: " . $strategy['title'] . "\n";
        echo "  Video ID: " . ($strategy['video_id'] ?? 'NULL') . "\n";
        echo "  Image ID: " . ($strategy['cover_image_id'] ?? 'NULL') . "\n";
    } else {
        echo "✗ Estratégia não encontrada no banco após inserção\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erro na inserção: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Limpar arquivos de teste
if (file_exists(__DIR__ . '/test_image.jpg')) {
    unlink(__DIR__ . '/test_image.jpg');
}
if (file_exists(__DIR__ . '/test_video.mp4')) {
    unlink(__DIR__ . '/test_video.mp4');
}

echo "\n=== Debug concluído ===\n";