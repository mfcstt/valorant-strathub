<?php

require_once 'database.php';
require_once 'functions.php';

echo "🔍 Testando configuração do banco...\n\n";

// Mostrar configuração atual
$config = config('database');
echo "📋 Configuração atual:\n";
echo "Driver: " . $config['driver'] . "\n";
echo "Host: " . ($config['host'] ?? 'N/A') . "\n";
echo "Database: " . ($config['dbname'] ?? 'N/A') . "\n";
echo "User: " . ($config['user'] ?? 'N/A') . "\n\n";

// Verificar extensões PHP
echo "🔧 Extensões PHP disponíveis:\n";
$extensions = get_loaded_extensions();
$pdo_extensions = array_filter($extensions, function($ext) {
    return strpos($ext, 'pdo') !== false;
});
foreach ($pdo_extensions as $ext) {
    echo "✅ $ext\n";
}

if (!in_array('pdo_pgsql', $extensions)) {
    echo "❌ pdo_pgsql (PostgreSQL) - NÃO INSTALADA\n";
    echo "\n💡 Para instalar no Windows:\n";
    echo "1. Abra o php.ini\n";
    echo "2. Descomente a linha: extension=pgsql\n";
    echo "3. Descomente a linha: extension=pdo_pgsql\n";
    echo "4. Reinicie o servidor web\n\n";
}

echo "\n🔗 Testando conexão...\n";

try {
    $database = new Database($config);
    echo "✅ Conexão estabelecida com sucesso!\n";
    
    // Testar uma query simples
    if ($config['driver'] === 'sqlite') {
        $result = $database->query("SELECT sqlite_version() as version")->fetch();
        echo "📊 Versão SQLite: " . $result->version . "\n";
    } elseif ($config['driver'] === 'pgsql') {
        $result = $database->query("SELECT version() as version")->fetch();
        echo "📊 Versão PostgreSQL: " . substr($result->version, 0, 50) . "...\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'could not find driver') !== false) {
        echo "\n🔧 SOLUÇÃO:\n";
        echo "1. Instale a extensão PostgreSQL do PHP\n";
        echo "2. Ou altere temporariamente para SQLite no config.php\n";
    }
}
