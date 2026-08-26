-- Rollback de 2026_08_25_000001_add_moderation.sql

ALTER TABLE strategies DROP COLUMN IF EXISTS moderation_note;
ALTER TABLE strategies DROP CONSTRAINT IF EXISTS strategies_status_check;
ALTER TABLE strategies DROP COLUMN IF EXISTS status;
ALTER TABLE users DROP COLUMN IF EXISTS is_admin;
