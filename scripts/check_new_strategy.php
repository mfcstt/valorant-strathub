<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';

$database = new Database(config('database')['database']);

$result = $database->query("SELECT COUNT(*) as count FROM estrategias WHERE title = 'Estratégia de Teste Completa'");
$count = $result->fetch(PDO::FETCH_ASSOC)['count'];

echo "Estratégias com título 'Estratégia de Teste Completa': " . $count . "\n";

// Verificar última estratégia criada
$result = $database->query("SELECT * FROM estrategias ORDER BY created_at DESC LIMIT 1");
$latest = $result->fetch(PDO::FETCH_ASSOC);

if ($latest) {
    echo "\nÚltima estratégia criada:\n";
    echo "ID: " . $latest['id'] . "\n";
    echo "Título: " . $latest['title'] . "\n";
    echo "Video ID: " . ($latest['video_id'] ?? 'NULL') . "\n";
    echo "Image ID: " . ($latest['cover_image_id'] ?? 'NULL') . "\n";
    echo "Criada em: " . $latest['created_at'] . "\n";
} else {
    echo "Nenhuma estratégia encontrada.\n";
}