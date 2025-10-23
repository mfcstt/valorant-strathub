<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $dbConfig = config('database');
    $database = new Database($dbConfig);

    $driver = $dbConfig['driver'] ?? 'pgsql';
    echo "Driver: {$driver}\n";

    $hasColumn = false;

    if ($driver === 'sqlite') {
        $stmt = $database->query("PRAGMA table_info(users)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            if (strtolower($col['name']) === 'elo') {
                $hasColumn = true;
                break;
            }
        }
    } else {
        $stmt = $database->query(
            "SELECT column_name FROM information_schema.columns WHERE table_name = 'users' AND column_name = 'elo'"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $hasColumn = !empty($rows);
    }

    if ($hasColumn) {
        echo "Coluna 'elo' já existe na tabela users. Nada a fazer.\n";
    } else {
        echo "Adicionando coluna 'elo' à tabela users...\n";
        if ($driver === 'sqlite') {
            $database->query("ALTER TABLE users ADD COLUMN elo VARCHAR(50) DEFAULT 'ferro'");
        } else {
            $database->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS elo VARCHAR(50) DEFAULT 'ferro'");
        }
        echo "Coluna adicionada com sucesso.\n";
    }

    echo "Atualizando usuários existentes sem elo para valor padrão...\n";
    if ($driver === 'sqlite') {
        $database->query("UPDATE users SET elo = COALESCE(elo, 'ferro')");
    } else {
        $database->query("UPDATE users SET elo = COALESCE(elo, 'ferro') WHERE elo IS NULL");
    }
    echo "Atualização concluída.\n";

    echo "Migração finalizada com sucesso.\n";
} catch (Exception $e) {
    http_response_code(500);
    echo "Erro na migração: " . $e->getMessage() . "\n";
}