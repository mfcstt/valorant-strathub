-- =============================================================================
-- Valorant StratHub — schema SQLite (desenvolvimento local)
--
-- Aplicado automaticamente pela aplicação na primeira execução quando
-- USE_SQLITE=true. Espelha database/schema.pgsql.sql; as diferenças são só de
-- dialeto (AUTOINCREMENT em vez de SERIAL, DATETIME em vez de TIMESTAMP e
-- triggers em vez de função PL/pgSQL para o updated_at).
-- =============================================================================

CREATE TABLE IF NOT EXISTS users (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       VARCHAR(255) NOT NULL,
    email      VARCHAR(255) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    avatar     VARCHAR(500) DEFAULT 'avatarDefault.png',
    elo        VARCHAR(50)  DEFAULT 'ferro',
    is_admin   INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS remember_tokens (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id        INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    selector       VARCHAR(64) UNIQUE NOT NULL,
    validator_hash VARCHAR(64) NOT NULL,
    expires_at     DATETIME NOT NULL,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS agents (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       VARCHAR(100) UNIQUE NOT NULL,
    photo      VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS maps (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       VARCHAR(100) UNIQUE NOT NULL,
    image      VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS images (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path     VARCHAR(500) NOT NULL,
    file_size     INTEGER NOT NULL,
    mime_type     VARCHAR(100) NOT NULL,
    user_id       INTEGER REFERENCES users(id) ON DELETE CASCADE,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS videos (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path     VARCHAR(500) NOT NULL,
    file_size     INTEGER NOT NULL,
    mime_type     VARCHAR(100) NOT NULL,
    duration      INTEGER,
    user_id       INTEGER REFERENCES users(id) ON DELETE CASCADE,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Moderação: toda estratégia nova nasce "pending" e só fica visível para
-- outras pessoas depois de aprovada por um admin (App\Support\Auth::isAdmin()).
-- O autor sempre continua vendo a própria, em qualquer status.
CREATE TABLE IF NOT EXISTS strategies (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    title           VARCHAR(255) NOT NULL,
    category        VARCHAR(100) NOT NULL,
    description     TEXT,
    cover_image_id  INTEGER REFERENCES images(id) ON DELETE SET NULL,
    video_id        INTEGER REFERENCES videos(id) ON DELETE SET NULL,
    user_id         INTEGER REFERENCES users(id) ON DELETE CASCADE,
    agent_id        INTEGER REFERENCES agents(id) ON DELETE SET NULL,
    map_id          INTEGER REFERENCES maps(id) ON DELETE SET NULL,
    status          VARCHAR(20) NOT NULL DEFAULT 'pending'
                    CHECK (status IN ('pending', 'approved', 'rejected')),
    moderation_note TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ratings (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    strategy_id INTEGER NOT NULL REFERENCES strategies(id) ON DELETE CASCADE,
    rating      INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment     TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, strategy_id)
);

-- Sessões (mesmo motivo do schema PostgreSQL — ver DatabaseSessionHandler).
CREATE TABLE IF NOT EXISTS sessions (
    id             VARCHAR(128) PRIMARY KEY,
    payload        TEXT NOT NULL,
    last_activity  INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions (last_activity);

CREATE TABLE IF NOT EXISTS favorites (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    strategy_id INTEGER NOT NULL REFERENCES strategies(id) ON DELETE CASCADE,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, strategy_id)
);

CREATE INDEX IF NOT EXISTS idx_users_email             ON users (email);
CREATE INDEX IF NOT EXISTS idx_remember_tokens_user    ON remember_tokens (user_id);
CREATE INDEX IF NOT EXISTS idx_remember_tokens_expires ON remember_tokens (expires_at);
CREATE INDEX IF NOT EXISTS idx_images_user_id          ON images (user_id);
CREATE INDEX IF NOT EXISTS idx_videos_user_id          ON videos (user_id);
CREATE INDEX IF NOT EXISTS idx_strategies_user_id      ON strategies (user_id);
CREATE INDEX IF NOT EXISTS idx_strategies_agent_id     ON strategies (agent_id);
CREATE INDEX IF NOT EXISTS idx_strategies_map_id       ON strategies (map_id);
CREATE INDEX IF NOT EXISTS idx_strategies_created_at   ON strategies (created_at DESC);
CREATE INDEX IF NOT EXISTS idx_ratings_strategy_id     ON ratings (strategy_id);
CREATE INDEX IF NOT EXISTS idx_ratings_user_id         ON ratings (user_id);
CREATE INDEX IF NOT EXISTS idx_favorites_user_id       ON favorites (user_id);
CREATE INDEX IF NOT EXISTS idx_favorites_strategy_id   ON favorites (strategy_id);

CREATE TRIGGER IF NOT EXISTS users_updated_at
AFTER UPDATE ON users FOR EACH ROW
BEGIN
    UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = OLD.id;
END;

CREATE TRIGGER IF NOT EXISTS strategies_updated_at
AFTER UPDATE ON strategies FOR EACH ROW
BEGIN
    UPDATE strategies SET updated_at = CURRENT_TIMESTAMP WHERE id = OLD.id;
END;

CREATE TRIGGER IF NOT EXISTS ratings_updated_at
AFTER UPDATE ON ratings FOR EACH ROW
BEGIN
    UPDATE ratings SET updated_at = CURRENT_TIMESTAMP WHERE id = OLD.id;
END;
