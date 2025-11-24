<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';
require_once 'Flash.php';
require_once 'Validation.php';
require_once 'src/models/Video.php';
require_once 'src/models/Image.php';

echo "=== Teste de Criação de Estratégia ===\n\n";

// Simular dados de POST para criação de estratégia
$_POST = [
    'titulo' => 'Estratégia de Teste',
    'categoria' => 'ataque',
    'descricao' => 'Esta é uma estratégia de teste para verificar se o upload funciona.',
    'agente' => '1', // ID do primeiro agente
    'mapa' => '1'    // ID do primeiro mapa
];

// Simular upload de imagem
$_FILES['capa'] = [
    'name' => 'test_cover.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => __DIR__ . '/test_image.jpg',
    'error' => UPLOAD_ERR_OK,
    'size' => 1024
];

// Simular upload de vídeo
$_FILES['video'] = [
    'name' => 'test_video.mp4',
    'type' => 'video/mp4',
    'tmp_name' => __DIR__ . '/test_video.mp4',
    'error' => UPLOAD_ERR_OK,
    'size' => 2048
];

// Criar arquivos de teste
echo "Criando arquivos de teste...\n";

// Criar imagem de teste (JPEG simples)
$imageData = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A');
file_put_contents(__DIR__ . '/test_image.jpg', $imageData);

// Criar vídeo de teste (MP4 mínimo)
$videoData = base64_decode('AAAAIGZ0eXBpc29tAAACAGlzb21pc28yYXZjMW1wNDEAAAAIZnJlZQAAACtmZGF0AAAA');
file_put_contents(__DIR__ . '/test_video.mp4', $videoData);

echo "Arquivos de teste criados.\n\n";

// Simular sessão de usuário logado
session_start();
$_SESSION['user'] = [
    'id' => 1,
    'name' => 'Usuário Teste',
    'email' => 'gui0307lipe@gmail.com'
];

echo "Simulando criação de estratégia...\n";

try {
    // Incluir o controller
    ob_start();
    include 'src/controllers/strategy-create.controller.php';
    $output = ob_get_clean();
    
    echo "Controller executado com sucesso!\n";
    echo "Output: " . $output . "\n";
    
} catch (Exception $e) {
    echo "Erro ao executar controller: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Limpar arquivos de teste
echo "\nLimpando arquivos de teste...\n";
if (file_exists(__DIR__ . '/test_image.jpg')) {
    unlink(__DIR__ . '/test_image.jpg');
}
if (file_exists(__DIR__ . '/test_video.mp4')) {
    unlink(__DIR__ . '/test_video.mp4');
}

echo "Teste concluído.\n";