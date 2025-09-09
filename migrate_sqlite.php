<?php

require_once 'database.php';
require_once 'functions.php';

try {
    echo "🚀 Iniciando migração para SQLite...\n\n";
    
    // Testar conexão
    $database = new Database(config('database'));
    echo "✅ Conexão com SQLite estabelecida!\n\n";
    
    // Schema SQLite (adaptado do PostgreSQL)
    $sql = "
    -- Tabela de usuários
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        avatar TEXT DEFAULT 'avatarDefault.png',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Tabela de agentes
    CREATE TABLE IF NOT EXISTS agents (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        photo TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Tabela de mapas
    CREATE TABLE IF NOT EXISTS maps (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        image TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Tabela de estratégias
    CREATE TABLE IF NOT EXISTS estrategias (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        category TEXT NOT NULL,
        description TEXT,
        cover TEXT,
        user_id INTEGER,
        agent_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL
    );

    -- Tabela de avaliações
    CREATE TABLE IF NOT EXISTS ratings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        estrategia_id INTEGER,
        rating INTEGER CHECK (rating >= 1 AND rating <= 5) NOT NULL,
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (estrategia_id) REFERENCES estrategias(id) ON DELETE CASCADE,
        UNIQUE(user_id, estrategia_id)
    );
    ";
    
    // Executar schema
    $database->query($sql);
    echo "✓ Schema criado com sucesso!\n";
    
    // Inserir agentes padrão
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
        $stmt = $database->query(
            "INSERT OR IGNORE INTO agents (name, photo) VALUES (?, ?)",
            null,
            [$agent[0], $agent[1]]
        );
    }
    echo "✓ Agentes inseridos!\n";
    
    // Criar índices
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_estrategias_user_id ON estrategias(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_estrategias_agent_id ON estrategias(agent_id)",
        "CREATE INDEX IF NOT EXISTS idx_ratings_estrategia_id ON ratings(estrategia_id)",
        "CREATE INDEX IF NOT EXISTS idx_ratings_user_id ON ratings(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)"
    ];
    
    foreach ($indexes as $index) {
        $database->query($index);
    }
    echo "✓ Índices criados!\n";
    
    echo "\n🎉 Migração SQLite concluída com sucesso!\n";
    echo "📊 Tabelas criadas: users, agents, maps, estrategias, ratings\n";
    echo "👥 Agentes padrão inseridos\n\n";
    
    // Verificar se as tabelas foram criadas
    $tables = $database->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Tabelas no banco: " . implode(', ', $tables) . "\n";
    
    // Contar agentes
    $agentCount = $database->query("SELECT COUNT(*) as total FROM agents")->fetch();
    echo "👥 Total de agentes: " . $agentCount->total . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro na migração: " . $e->getMessage() . "\n";
    exit(1);
}
