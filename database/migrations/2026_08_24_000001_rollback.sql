-- =============================================================================
-- Rollback da migração 2026_08_24_000001 — PostgreSQL / Supabase
--
-- Desfaz as renomeações, devolvendo o schema ao formato que a versão anterior
-- da aplicação espera. Use se for preciso voltar o deploy para o código antigo.
--
--   psql "$DATABASE_URL" -f database/migrations/2026_08_24_000001_rollback.sql
--
-- O que este script NÃO desfaz, de propósito:
--
--   * `remember_tokens` continua existindo. A tabela é inerte para o código
--     antigo, e derrubá-la desconectaria todo mundo sem necessidade.
--   * `ratings.updated_at` continua existindo. Coluna com default não incomoda
--     o código antigo, e removê-la perderia dado.
--   * As fotos de Brimstone e Cypher continuam corrigidas. Voltar para
--     'brim.png' e 'chypher.png' recriaria as imagens quebradas — e o código
--     antigo também renderiza os nomes novos, desde que os arquivos existam.
--
-- Cada passo é condicional, então reexecutar é seguro.
-- =============================================================================

BEGIN;

-- 1. Colunas de referência ----------------------------------------------------
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns
                WHERE table_name = 'ratings' AND column_name = 'strategy_id')
    THEN
        ALTER TABLE ratings RENAME COLUMN strategy_id TO estrategia_id;
    END IF;

    IF EXISTS (SELECT 1 FROM information_schema.columns
                WHERE table_name = 'favorites' AND column_name = 'strategy_id')
    THEN
        ALTER TABLE favorites RENAME COLUMN strategy_id TO estrategia_id;
    END IF;
END $$;

-- 2. Trigger ------------------------------------------------------------------
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.triggers
                WHERE trigger_schema = 'public'
                  AND trigger_name = 'update_strategies_updated_at')
    THEN
        ALTER TRIGGER update_strategies_updated_at ON strategies
            RENAME TO update_estrategias_updated_at;
    END IF;
END $$;

-- 3. Tabela -------------------------------------------------------------------
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.tables
                WHERE table_schema = 'public' AND table_name = 'strategies')
       AND NOT EXISTS (SELECT 1 FROM information_schema.tables
                        WHERE table_schema = 'public' AND table_name = 'estrategias')
    THEN
        ALTER TABLE strategies RENAME TO estrategias;
    END IF;
END $$;

-- 4. Índices criados pela migração --------------------------------------------
DROP INDEX IF EXISTS idx_strategies_user_id;
DROP INDEX IF EXISTS idx_strategies_agent_id;
DROP INDEX IF EXISTS idx_strategies_map_id;
DROP INDEX IF EXISTS idx_strategies_created_at;
DROP INDEX IF EXISTS idx_ratings_strategy_id;
DROP INDEX IF EXISTS idx_favorites_strategy_id;

COMMIT;
