-- =============================================================================
-- Migração - PostgreSQL / Supabase
--
-- Adiciona a tabela de sessões. Sem ela, todo formulário do site (login,
-- cadastro, criar estratégia, avaliar, favoritar) devolve 419 em produção: o
-- handler de sessão padrão do PHP grava em arquivo local, e a Vercel pode
-- atender o GET que gera a página e o POST que envia o formulário em duas
-- instâncias serverless diferentes, sem disco compartilhado entre elas - a
-- sessão criada na primeira simplesmente não existe na segunda.
--
-- Puramente aditiva: cria uma tabela nova, não toca em nada existente.
-- Rodar uma vez:
--   psql "$DATABASE_URL" -f database/migrations/2026_08_24_000002_add_sessions_table.sql
-- =============================================================================

BEGIN;

CREATE TABLE IF NOT EXISTS sessions (
    id             VARCHAR(128) PRIMARY KEY,
    payload        TEXT NOT NULL,
    last_activity  INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions (last_activity);

COMMIT;
