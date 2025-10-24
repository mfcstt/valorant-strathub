<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';

echo "=== Detalhes das Estratégias ===\n\n";

$database = new Database(config('database')['database']);

try {
    // Verificar estratégias com seus vídeos
    $strategiesStmt = $database->query("
        SELECT e.id, e.title, e.user_id, e.video_id, e.cover_image_id, e.created_at,
               v.filename as video_filename, v.original_name as video_original_name,
               i.filename as image_filename, i.original_name as image_original_name
        FROM estrategias e
        LEFT JOIN videos v ON e.video_id = v.id
        LEFT JOIN images i ON e.cover_image_id = i.id
        ORDER BY e.created_at DESC 
        LIMIT 10
    ");
    $strategies = $strategiesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($strategies && is_array($strategies)) {
        echo "Últimas 10 estratégias com detalhes:\n\n";
        foreach ($strategies as $strategy) {
            echo "ID: {$strategy['id']}\n";
            echo "Título: {$strategy['title']}\n";
            echo "Usuário: {$strategy['user_id']}\n";
            echo "Video ID: " . ($strategy['video_id'] ?? 'NULL') . "\n";
            echo "Video Filename: " . ($strategy['video_filename'] ?? 'NULL') . "\n";
            echo "Video Original: " . ($strategy['video_original_name'] ?? 'NULL') . "\n";
            echo "Image ID: " . ($strategy['cover_image_id'] ?? 'NULL') . "\n";
            echo "Image Filename: " . ($strategy['image_filename'] ?? 'NULL') . "\n";
            echo "Image Original: " . ($strategy['image_original_name'] ?? 'NULL') . "\n";
            echo "Criada em: {$strategy['created_at']}\n";
            echo "---\n\n";
        }
    } else {
        echo "Nenhuma estratégia encontrada.\n";
    }
    
    // Verificar todos os vídeos
    echo "=== Todos os Vídeos ===\n\n";
    $videosStmt = $database->query("SELECT * FROM videos ORDER BY created_at DESC");
    $videos = $videosStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($videos && is_array($videos)) {
        foreach ($videos as $video) {
            echo "ID: {$video['id']}\n";
            echo "Filename: {$video['filename']}\n";
            echo "Original: {$video['original_name']}\n";
            echo "Path: {$video['path']}\n";
            echo "Size: {$video['size']}\n";
            echo "User ID: {$video['user_id']}\n";
            echo "Criado em: {$video['created_at']}\n";
            echo "---\n\n";
        }
    } else {
        echo "Nenhum vídeo encontrado.\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

echo "=== Verificação concluída ===\n";