-- =============================================================================
-- Valorant StratHub — schema PostgreSQL (Supabase)
--
-- Aplicar num banco vazio:
--   psql "$DATABASE_URL" -f database/schema.pgsql.sql
--
-- Para um banco que já roda a versão antiga, use as migrações em
-- database/migrations/ em vez deste arquivo.
-- =============================================================================

-- Usuários -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id         SERIAL PRIMARY KEY,
    name       VARCHAR(255) NOT NULL,
    email      VARCHAR(255) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    avatar     VARCHAR(500) DEFAULT 'avatarDefault.png',
    elo        VARCHAR(50)  DEFAULT 'ferro',
    is_admin   BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tokens de "continuar conectado" --------------------------------------------
-- Padrão split-token: `selector` é público e indexado, `validator_hash` guarda
-- apenas o SHA-256 do segredo. Um vazamento da tabela não permite forjar login.
CREATE TABLE IF NOT EXISTS remember_tokens (
    id             SERIAL PRIMARY KEY,
    user_id        INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    selector       VARCHAR(64) UNIQUE NOT NULL,
    validator_hash VARCHAR(64) NOT NULL,
    expires_at     TIMESTAMP NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Agentes e mapas ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS agents (
    id         SERIAL PRIMARY KEY,
    name       VARCHAR(100) UNIQUE NOT NULL,
    photo      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS maps (
    id         SERIAL PRIMARY KEY,
    name       VARCHAR(100) UNIQUE NOT NULL,
    image      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Mídia ----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS images (
    id            SERIAL PRIMARY KEY,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path     VARCHAR(500) NOT NULL,
    file_size     INTEGER NOT NULL,
    mime_type     VARCHAR(100) NOT NULL,
    user_id       INTEGER REFERENCES users(id) ON DELETE CASCADE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS videos (
    id            SERIAL PRIMARY KEY,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path     VARCHAR(500) NOT NULL,
    file_size     INTEGER NOT NULL,
    mime_type     VARCHAR(100) NOT NULL,
    duration      INTEGER,
    user_id       INTEGER REFERENCES users(id) ON DELETE CASCADE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Estratégias ----------------------------------------------------------------
-- Moderação: toda estratégia nova nasce "pending" e só fica visível para
-- outras pessoas depois de aprovada por um admin (App\Support\Auth::isAdmin()).
-- O autor sempre continua vendo a própria, em qualquer status.
CREATE TABLE IF NOT EXISTS strategies (
    id              SERIAL PRIMARY KEY,
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
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Avaliações -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ratings (
    id          SERIAL PRIMARY KEY,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    strategy_id INTEGER NOT NULL REFERENCES strategies(id) ON DELETE CASCADE,
    rating      INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment     TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, strategy_id)
);

-- Sessões -------------------------------------------------------------------
-- Guardadas no banco, não em arquivo: em hospedagem serverless (Vercel) cada
-- requisição pode cair numa instância sem disco compartilhado com a anterior.
-- Ver App\Support\DatabaseSessionHandler.
CREATE TABLE IF NOT EXISTS sessions (
    id             VARCHAR(128) PRIMARY KEY,
    payload        TEXT NOT NULL,
    last_activity  INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions (last_activity);

-- Favoritas ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS favorites (
    id          SERIAL PRIMARY KEY,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    strategy_id INTEGER NOT NULL REFERENCES strategies(id) ON DELETE CASCADE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, strategy_id)
);

-- Índices --------------------------------------------------------------------
CREATE INDEX IF NOT EXISTS idx_users_email              ON users (email);
CREATE INDEX IF NOT EXISTS idx_remember_tokens_user     ON remember_tokens (user_id);
CREATE INDEX IF NOT EXISTS idx_remember_tokens_expires  ON remember_tokens (expires_at);
CREATE INDEX IF NOT EXISTS idx_images_user_id           ON images (user_id);
CREATE INDEX IF NOT EXISTS idx_videos_user_id           ON videos (user_id);
CREATE INDEX IF NOT EXISTS idx_strategies_user_id       ON strategies (user_id);
CREATE INDEX IF NOT EXISTS idx_strategies_agent_id      ON strategies (agent_id);
CREATE INDEX IF NOT EXISTS idx_strategies_map_id        ON strategies (map_id);
CREATE INDEX IF NOT EXISTS idx_strategies_created_at    ON strategies (created_at DESC);
CREATE INDEX IF NOT EXISTS idx_strategies_title_lower   ON strategies (LOWER(title));
CREATE INDEX IF NOT EXISTS idx_strategies_category_low  ON strategies (LOWER(category));
CREATE INDEX IF NOT EXISTS idx_agents_name_lower        ON agents (LOWER(name));
CREATE INDEX IF NOT EXISTS idx_maps_name_lower          ON maps (LOWER(name));
CREATE INDEX IF NOT EXISTS idx_ratings_strategy_id      ON ratings (strategy_id);
CREATE INDEX IF NOT EXISTS idx_ratings_user_id          ON ratings (user_id);
CREATE INDEX IF NOT EXISTS idx_favorites_user_id        ON favorites (user_id);
CREATE INDEX IF NOT EXISTS idx_favorites_strategy_id    ON favorites (strategy_id);

-- Atualização automática de updated_at ---------------------------------------
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS update_users_updated_at ON users;
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_strategies_updated_at ON strategies;
CREATE TRIGGER update_strategies_updated_at BEFORE UPDATE ON strategies
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_ratings_updated_at ON ratings;
CREATE TRIGGER update_ratings_updated_at BEFORE UPDATE ON ratings
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
