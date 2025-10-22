<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';

echo "=== Verificação de Estratégias ===\n\n";

$database = new Database(config('database')['database']);

try {
    // Contar estratégias
    $countStmt = $database->query("SELECT COUNT(*) as total FROM estrategias");
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de estratégias: " . ($countResult['total'] ?? 'erro') . "\n\n";
    
    // Listar últimas estratégias
    $strategiesStmt = $database->query("SELECT * FROM estrategias ORDER BY created_at DESC LIMIT 5");
    $strategies = $strategiesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($strategies && is_array($strategies)) {
        echo "Últimas 5 estratégias:\n";
        foreach ($strategies as $strategy) {
            echo "- ID: {$strategy['id']}, Título: {$strategy['title']}, Usuário: {$strategy['user_id']}, Criada em: {$strategy['created_at']}\n";
        }
    } else {
        echo "Nenhuma estratégia encontrada.\n";
    }
    
    echo "\n";
    
    // Verificar uploads de vídeo
    $videosStmt = $database->query("SELECT COUNT(*) as total FROM videos");
    $videosResult = $videosStmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de vídeos: " . ($videosResult['total'] ?? 'erro') . "\n";
    
    // Verificar uploads de imagem
    $imagesStmt = $database->query("SELECT COUNT(*) as total FROM images");
    $imagesResult = $imagesStmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de imagens: " . ($imagesResult['total'] ?? 'erro') . "\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

echo "\n=== Verificação concluída ===\n";