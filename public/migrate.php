<?php

require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../database.php';

try {
    $config = require __DIR__ . '/../config.php';
    $dbConf = $config['database'];
    $database = new Database($dbConf);
    
    // Escolhe schema conforme driver
    $schemaFile = $dbConf['driver'] === 'sqlite' 
        ? __DIR__ . '/../database_schema_sqlite.sql' 
        : __DIR__ . '/../database_schema.sql';

    if (!file_exists($schemaFile)) {
        throw new Exception('Arquivo de schema não encontrado: ' . $schemaFile);
    }

    // Ler o arquivo SQL
    $sql = file_get_contents($schemaFile);
    
    // Dividir em comandos individuais
    $commands = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($commands as $command) {
        if (!empty($command)) {
            $database->query($command);
            echo "✓ Executado: " . substr($command, 0, 50) . "...\n";
        }
    }
    
    echo "\n🎉 Migração concluída com sucesso!\n";
} catch (Exception $e) {
    echo "❌ Erro na migração: " . $e->getMessage() . "\n";
}
