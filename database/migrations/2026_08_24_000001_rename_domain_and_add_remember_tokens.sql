-- =============================================================================
-- Migração — PostgreSQL / Supabase
--
-- Alinha um banco que roda a versão antiga ao schema atual:
--
--   1. `estrategias` passa a `strategies` e `estrategia_id` a `strategy_id`,
--      eliminando a mistura de português e inglês no schema.
--   2. Cria `remember_tokens`, que substitui o cookie `auth_uid` + HMAC.
--   3. Cria `favorites` caso ela tenha sido criada à mão no painel do Supabase
--      e não exista neste banco.
--   4. Corrige os nomes de arquivo das fotos de Brimstone e Cypher, que
--      apontavam para arquivos inexistentes.
--
-- Rodar uma única vez:
--   psql "$DATABASE_URL" -f database/migrations/2026_08_24_000001_...sql
--
-- Cada passo é condicional, então reexecutar é seguro.
-- =============================================================================

BEGIN;

-- 1. Renomear a tabela de estratégias -----------------------------------------
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.tables
                WHERE table_schema = 'public' AND table_name = 'estrategias')
       AND NOT EXISTS (SELECT 1 FROM information_schema.tables
                        WHERE table_schema = 'public' AND table_name = 'strategies')
    THEN
        ALTER TABLE estrategias RENAME TO strategies;
    END IF;
END $$;

-- 2. Renomear as colunas de referência ----------------------------------------
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns
                WHERE table_name = 'ratings' AND column_name = 'estrategia_id')
    THEN
        ALTER TABLE ratings RENAME COLUMN estrategia_id TO strategy_id;
    END IF;

    IF EXISTS (SELECT 1 FROM information_schema.columns
                WHERE table_name = 'favorites' AND column_name = 'estrategia_id')
    THEN
        ALTER TABLE favorites RENAME COLUMN estrategia_id TO strategy_id;
    END IF;
END $$;

-- 3. Coluna updated_at em ratings ---------------------------------------------
ALTER TABLE ratings ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- 4. Favoritas (caso ainda não exista) ----------------------------------------
CREATE TABLE IF NOT EXISTS favorites (
    id          SERIAL PRIMARY KEY,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    strategy_id INTEGER NOT NULL REFERENCES strategies(id) ON DELETE CASCADE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, strategy_id)
);

-- 5. Tokens de "continuar conectado" -----------------------------------------
CREATE TABLE IF NOT EXISTS remember_tokens (
    id             SERIAL PRIMARY KEY,
    user_id        INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    selector       VARCHAR(64) UNIQUE NOT NULL,
    validator_hash VARCHAR(64) NOT NULL,
    expires_at     TIMESTAMP NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Índices do novo nome -----------------------------------------------------
CREATE INDEX IF NOT EXISTS idx_strategies_user_id      ON strategies (user_id);
CREATE INDEX IF NOT EXISTS idx_strategies_agent_id     ON strategies (agent_id);
CREATE INDEX IF NOT EXISTS idx_strategies_map_id       ON strategies (map_id);
CREATE INDEX IF NOT EXISTS idx_strategies_created_at   ON strategies (created_at DESC);
CREATE INDEX IF NOT EXISTS idx_ratings_strategy_id     ON ratings (strategy_id);
CREATE INDEX IF NOT EXISTS idx_favorites_user_id       ON favorites (user_id);
CREATE INDEX IF NOT EXISTS idx_favorites_strategy_id   ON favorites (strategy_id);
CREATE INDEX IF NOT EXISTS idx_remember_tokens_user    ON remember_tokens (user_id);
CREATE INDEX IF NOT EXISTS idx_remember_tokens_expires ON remember_tokens (expires_at);

-- 7. Fotos de agentes que apontavam para arquivos inexistentes ---------------
UPDATE agents SET photo = 'brimstone.png' WHERE name = 'Brimstone' AND photo <> 'brimstone.png';
UPDATE agents SET photo = 'cypher.png'    WHERE name = 'Cypher'    AND photo <> 'cypher.png';

COMMIT;
