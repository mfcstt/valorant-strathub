<?php

require_once __DIR__ . '/database.php';

try {
    $config = require __DIR__ . '/config.php';
    $dbConf = $config['database'];
    $database = new Database($dbConf);
    $driver = $dbConf['driver'] ?? 'pgsql';

    $mapsDir = __DIR__ . '/public/assets/images/maps';
    if (!is_dir($mapsDir)) {
        throw new Exception('Pasta de mapas não encontrada: ' . $mapsDir);
    }

    $files = glob($mapsDir . '/*.{png,jpg,jpeg}', GLOB_BRACE);
    if (!$files) {
        echo "Nenhuma imagem de mapa encontrada em $mapsDir\n";
        exit(0);
    }

    $insertSql = $driver === 'sqlite'
        ? 'INSERT OR IGNORE INTO maps (name, image) VALUES (:name, :image)'
        : 'INSERT INTO maps (name, image) VALUES (:name, :image) ON CONFLICT (name) DO NOTHING';

    $count = 0;
    foreach ($files as $filePath) {
        $filename = basename($filePath);
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $name = ucwords(str_replace(['-', '_'], ' ', strtolower($base)));

        $database->query($insertSql, null, [
            ':name' => $name,
            ':image' => $filename,
        ]);
        echo "✓ Mapa '$name' inserido/vinculado com imagem '$filename'\n";
        $count++;
    }

    echo "\n🎉 Concluído: $count mapas processados.\n";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}