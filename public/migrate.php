<?php

require_once 'Database.php';

try {
    $database = new Database(config('database')['database']);
    
    // Ler o arquivo SQL
    $sql = file_get_contents('database_schema.sql');
    
    // Dividir em comandos individuais
    $commands = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($commands as $command) {
        if (!empty($command)) {
            $database->query($command);
            echo "✓ Executado: " . substr($command, 0, 50) . "...\n";
        }
    }
    
    echo "\n🎉 Migração concluída com sucesso!\n";
    echo "Tabelas criadas: users, movies, ratings\n";
    
} catch (Exception $e) {
    echo "❌ Erro na migração: " . $e->getMessage() . "\n";
}
