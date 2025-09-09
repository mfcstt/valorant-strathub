<?php
// Incluir config.php
$config = require 'config.php';

// Pegar configuração do banco
$cfg = $config['database'];

try {
    // Criar DSN para PostgreSQL
    $dsn = "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};sslmode={$cfg['sslmode']}";
    $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✅ Conexão com Supabase PostgreSQL estabelecida!\n";
} catch (PDOException $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "\n";
}
