-- =============================================================================
-- Moderação de estratégias.
--
-- Aditiva e segura para rodar em produção sem downtime:
--   - `users.is_admin` nasce FALSE em todo mundo — ninguém vira admin sozinho.
--   - `strategies.status` nasce 'pending' em toda linha existente, inclusive
--     nas já publicadas hoje (decisão do dono do projeto: colocar tudo em
--     análise para testar o fluxo de aprovação do zero).
-- =============================================================================

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS is_admin BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE strategies
    ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'pending';

ALTER TABLE strategies
    ADD CONSTRAINT strategies_status_check
    CHECK (status IN ('pending', 'approved', 'rejected'));

ALTER TABLE strategies
    ADD COLUMN IF NOT EXISTS moderation_note TEXT;
