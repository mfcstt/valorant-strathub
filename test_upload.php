<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';
require_once 'src/services/SupabaseStorageService.php';
require_once 'src/models/Video.php';

echo "=== Teste de Upload de Vídeo ===\n\n";

// Criar um arquivo de teste pequeno
$testContent = "Este é um arquivo de teste para simular um vídeo MP4";
$testFile = sys_get_temp_dir() . '/test_video.mp4';
file_put_contents($testFile, $testContent);

// Simular dados de $_FILES
$fakeFile = [
    'name' => 'test_video.mp4',
    'type' => 'video/mp4',
    'tmp_name' => $testFile,
    'error' => UPLOAD_ERR_OK,
    'size' => strlen($testContent)
];

echo "Arquivo de teste criado: {$testFile}\n";
echo "Tamanho: " . strlen($testContent) . " bytes\n\n";

try {
    $storageService = new SupabaseStorageService();
    
    echo "Iniciando upload de teste...\n";
    $result = $storageService->uploadVideo($fakeFile, 1); // user_id = 1
    
    if ($result) {
        echo "✓ Upload realizado com sucesso!\n";
        echo "ID do vídeo: {$result->id}\n";
        echo "Nome do arquivo: {$result->filename}\n";
        echo "Caminho: {$result->file_path}\n";
        
        // Testar URL pública
        $publicUrl = $storageService->getVideoPublicUrl($result->file_path);
        echo "URL pública: {$publicUrl}\n";
        
        // Testar se o arquivo está acessível
        echo "\nTestando acesso ao arquivo...\n";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $publicUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Status do arquivo: HTTP {$httpCode}\n";
        if ($httpCode === 200) {
            echo "✓ Arquivo acessível publicamente\n";
        } else {
            echo "✗ Arquivo não acessível\n";
        }
        
    } else {
        echo "✗ Falha no upload\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erro durante o upload: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Limpar arquivo de teste
unlink($testFile);
echo "\nArquivo de teste removido.\n";