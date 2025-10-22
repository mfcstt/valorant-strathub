<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';
require_once 'Flash.php';
require_once 'Validation.php';
require_once 'src/models/Video.php';
require_once 'src/models/Image.php';
require_once 'src/services/SupabaseStorageService.php';

echo "=== Teste Detalhado de Upload de Vídeo ===\n\n";

// Criar vídeo de teste maior
echo "Criando vídeo de teste...\n";
$videoData = str_repeat('AAAAIGZ0eXBpc29tAAACAGlzb21pc28yYXZjMW1wNDEAAAAIZnJlZQAAACtmZGF0AAAA', 100);
file_put_contents(__DIR__ . '/test_video_detailed.mp4', base64_decode($videoData));

$testFile = [
    'name' => 'test_video_detailed.mp4',
    'type' => 'video/mp4',
    'tmp_name' => __DIR__ . '/test_video_detailed.mp4',
    'error' => UPLOAD_ERR_OK,
    'size' => filesize(__DIR__ . '/test_video_detailed.mp4')
];

echo "Arquivo criado: " . $testFile['size'] . " bytes\n\n";

try {
    echo "Iniciando upload...\n";
    
    $storageService = new SupabaseStorageService();
    $uploadedVideo = $storageService->uploadVideo($testFile, 1);
    
    if ($uploadedVideo) {
        echo "✓ Upload realizado com sucesso!\n";
        echo "Video ID: " . $uploadedVideo->id . "\n";
        echo "Filename: " . $uploadedVideo->filename . "\n";
        echo "Original Name: " . $uploadedVideo->original_name . "\n";
        
        // Verificar se o vídeo está acessível
        $publicUrl = "https://" . config('supabase')['project_id'] . ".supabase.co/storage/v1/object/public/strategy-videos/" . $uploadedVideo->filename;
        echo "URL pública: " . $publicUrl . "\n";
        
        // Testar acesso
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $publicUrl);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Status HTTP: " . $httpCode . "\n";
        
        if ($httpCode == 200) {
            echo "✓ Vídeo acessível publicamente!\n";
        } else {
            echo "✗ Vídeo não acessível (HTTP $httpCode)\n";
        }
        
    } else {
        echo "✗ Falha no upload - retorno NULL\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erro durante upload: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Limpar arquivo de teste
if (file_exists(__DIR__ . '/test_video_detailed.mp4')) {
    unlink(__DIR__ . '/test_video_detailed.mp4');
    echo "\nArquivo de teste removido.\n";
}

echo "\n=== Teste concluído ===\n";