-- Rollback da migração 2026_08_24_000002 - PostgreSQL / Supabase
--
-- Remove a tabela de sessões. Só necessário se for reverter para uma versão do
-- código anterior à introdução do DatabaseSessionHandler - a versão atual do
-- código não sobe sem essa tabela.
--
--   psql "$DATABASE_URL" -f database/migrations/2026_08_24_000002_rollback.sql

BEGIN;

DROP TABLE IF EXISTS sessions;

COMMIT;
