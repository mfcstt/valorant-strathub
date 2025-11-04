-- Schema para SQLite (teste local)

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'avatarDefault.png',
    elo VARCHAR(50) DEFAULT 'ferro',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de agentes
CREATE TABLE IF NOT EXISTS agents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    photo VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de mapas
CREATE TABLE IF NOT EXISTS maps (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de imagens
CREATE TABLE IF NOT EXISTS images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INTEGER NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de vídeos
CREATE TABLE IF NOT EXISTS videos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INTEGER NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    duration INTEGER, -- duração em segundos
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de estratégias
CREATE TABLE IF NOT EXISTS estrategias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    cover_image_id INTEGER REFERENCES images(id) ON DELETE SET NULL,
    video_id INTEGER REFERENCES videos(id) ON DELETE SET NULL,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    agent_id INTEGER REFERENCES agents(id) ON DELETE SET NULL,
    map_id INTEGER REFERENCES maps(id) ON DELETE SET NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de avaliações
CREATE TABLE IF NOT EXISTS ratings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    estrategia_id INTEGER REFERENCES estrategias(id) ON DELETE CASCADE,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5) NOT NULL,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, estrategia_id)
);

-- Inserir agentes padrão
INSERT OR IGNORE INTO agents (name, photo) VALUES
('Astra', 'astra.png'),
('Breach', 'breach.png'),
('Brimstone', 'brimstone.png'),
('Chamber', 'chamber.png'),
('Clove', 'clove.png'),
('Cypher', 'cypher.png'),
('Deadlock', 'deadlock.png'),
('Fade', 'fade.png'),
('Gekko', 'gekko.png'),
('Harbor', 'harbor.png'),
('Iso', 'iso.png'),
('Jett', 'jett.png'),
('KAY/O', 'kayo.png'),
('Killjoy', 'killjoy.png'),
('Neon', 'neon.png'),
('Omen', 'omen.png'),
('Phoenix', 'phoenix.png'),
('Raze', 'raze.png'),
('Reyna', 'reyna.png'),
('Sage', 'sage.png'),
('Skye', 'skye.png'),
('Sova', 'sova.png'),
('Tejo', 'tejo.png'),
('Veto', 'veto.png'),
('Viper', 'viper.png'),
('Vyse', 'vyse.png'),
('Waylay', 'waylay.png'),
('Yoru', 'yoru.png');

-- Índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_images_user_id ON images(user_id);
CREATE INDEX IF NOT EXISTS idx_images_filename ON images(filename);
CREATE INDEX IF NOT EXISTS idx_videos_user_id ON videos(user_id);
CREATE INDEX IF NOT EXISTS idx_videos_filename ON videos(filename);
CREATE INDEX IF NOT EXISTS idx_estrategias_user_id ON estrategias(user_id);
CREATE INDEX IF NOT EXISTS idx_estrategias_agent_id ON estrategias(agent_id);
CREATE INDEX IF NOT EXISTS idx_estrategias_map_id ON estrategias(map_id);
CREATE INDEX IF NOT EXISTS idx_estrategias_cover_image_id ON estrategias(cover_image_id);
CREATE INDEX IF NOT EXISTS idx_estrategias_video_id ON estrategias(video_id);
CREATE INDEX IF NOT EXISTS idx_estrategias_cover_image_id ON estrategias(cover_image_id);
CREATE INDEX IF NOT EXISTS idx_estrategias_created_at ON estrategias(created_at);
CREATE INDEX IF NOT EXISTS idx_estrategias_title_lower ON estrategias(lower(title));
CREATE INDEX IF NOT EXISTS idx_estrategias_category_lower ON estrategias(lower(category));
CREATE INDEX IF NOT EXISTS idx_agents_name_lower ON agents(lower(name));
CREATE INDEX IF NOT EXISTS idx_maps_name_lower ON maps(lower(name));
CREATE INDEX IF NOT EXISTS idx_ratings_estrategia_id ON ratings(estrategia_id);
CREATE INDEX IF NOT EXISTS idx_ratings_user_id ON ratings(user_id);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- Favoritos: relação usuário <-> estratégia
CREATE TABLE IF NOT EXISTS favorites (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  estrategia_id INTEGER NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(user_id, estrategia_id)
);

CREATE INDEX IF NOT EXISTS idx_favorites_user_id ON favorites(user_id);
CREATE INDEX IF NOT EXISTS idx_favorites_estrategia_id ON favorites(estrategia_id);
