<?php

require_once 'database.php';

// Carregar .env
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$config = require 'config.php';
$db = new Database($config['database']);

try {
    echo "🚀 Iniciando migração para Supabase...\n\n";

    // ----------------------
    // Criar tabelas
    // ----------------------
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            avatar VARCHAR(255) DEFAULT 'avatarDefault.png',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS agents (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) UNIQUE NOT NULL,
            photo VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS maps (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) UNIQUE NOT NULL,
            image VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS estrategias (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            description TEXT,
            cover VARCHAR(255),
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            agent_id INTEGER REFERENCES agents(id) ON DELETE SET NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS ratings (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            estrategia_id INTEGER REFERENCES estrategias(id) ON DELETE CASCADE,
            rating INTEGER CHECK (rating >= 1 AND rating <= 5) NOT NULL,
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, estrategia_id)
        )"
    ];

    foreach ($tables as $tableSql) {
        $db->query($tableSql);
        echo "✓ Tabela criada ou já existe.\n";
    }

    // ----------------------
    // Criar índices
    // ----------------------
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_estrategias_user_id ON estrategias(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_estrategias_agent_id ON estrategias(agent_id)",
        "CREATE INDEX IF NOT EXISTS idx_ratings_estrategia_id ON ratings(estrategia_id)",
        "CREATE INDEX IF NOT EXISTS idx_ratings_user_id ON ratings(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)"
    ];

    foreach ($indexes as $indexSql) {
        $db->query($indexSql);
        echo "✓ Índice criado ou já existe.\n";
    }

    // ----------------------
    // Criar função de trigger
    // ----------------------
    $db->query("
        CREATE OR REPLACE FUNCTION update_updated_at_column()
        RETURNS TRIGGER AS \$func\$
        BEGIN
            NEW.updated_at = CURRENT_TIMESTAMP;
            RETURN NEW;
        END;
        \$func\$ LANGUAGE plpgsql
    ");
    echo "✓ Função de trigger criada.\n";

    // Criar triggers
    $triggers = [
        "DROP TRIGGER IF EXISTS update_users_updated_at ON users",
        "CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column()",
        "DROP TRIGGER IF EXISTS update_estrategias_updated_at ON estrategias",
        "CREATE TRIGGER update_estrategias_updated_at BEFORE UPDATE ON estrategias FOR EACH ROW EXECUTE FUNCTION update_updated_at_column()"
    ];

    foreach ($triggers as $triggerSql) {
        $db->query($triggerSql);
        echo "✓ Trigger configurada.\n";
    }

    // ----------------------
    // Inserir agentes padrão
    // ----------------------
    $agents = [
        ['Brimstone', 'brim.png'],
        ['Cypher', 'chypher.png'],
        ['Jett', 'jett.png'],
        ['Killjoy', 'killjoy.png'],
        ['Omen', 'omen.png'],
        ['Phoenix', 'phoenix.png'],
        ['Raze', 'raze.png'],
        ['Reyna', 'reyna.png'],
        ['Sage', 'sage.png'],
        ['Sova', 'sova.png'],
        ['Viper', 'viper.png']
    ];

    foreach ($agents as $agent) {
        $db->query(
            "INSERT INTO agents (name, photo) VALUES (:name, :photo) ON CONFLICT (name) DO NOTHING",
            null,
            [':name' => $agent[0], ':photo' => $agent[1]]
        );
        echo "✓ Agente {$agent[0]} inserido ou já existe.\n";
    }

    echo "\n🎉 Migração concluída com sucesso!\n";

} catch (Exception $e) {
    echo "❌ Erro na migração: " . $e->getMessage() . "\n";
}
