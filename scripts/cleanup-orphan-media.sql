-- =============================================================================
-- Limpeza de mídia órfã no Supabase - OPCIONAL, não roda automaticamente.
--
-- Identifica imagens e vídeos sem nenhuma referência viva: nem capa de
-- estratégia, nem avatar de usuário atual. A maioria data de outubro/novembro
-- de 2025, de antes da correção em ProfileActions.php e strategy-create.php -
-- o app antigo deixava um arquivo órfão sempre que um upload tinha sucesso e
-- o passo seguinte falhava (vídeo inválido, conta apagada, avatar trocado).
--
-- Este script SÓ identifica e mostra o que seria removido. Apagar de verdade
-- exige duas etapas manuais, nessa ordem:
--
--   1. Apagar os objetos do Storage (a API do Postgres não alcança o bucket):
--      para cada file_path abaixo, chamar
--        DELETE {SUPABASE_URL}/storage/v1/object/strategy-covers/{file_path}
--        DELETE {SUPABASE_URL}/storage/v1/object/strategy-videos/{file_path}
--      com header Authorization: Bearer {SUPABASE_SERVICE_KEY}
--
--   2. Só depois, com os arquivos já removidos, rodar o DELETE das linhas
--      (comentado no final deste arquivo).
--
-- Nenhuma estratégia ou avatar atualmente exibido no site referencia essas
-- linhas - apagá-las não muda nada visível, só libera espaço no Storage.
-- =============================================================================

-- Imagens órfãs (nem capa de estratégia, nem avatar de ninguém)
SELECT id, file_path, file_size, created_at
  FROM images i
 WHERE NOT EXISTS (SELECT 1 FROM strategies s WHERE s.cover_image_id = i.id)
   AND NOT EXISTS (SELECT 1 FROM users u WHERE u.avatar LIKE '%' || i.file_path)
 ORDER BY created_at;

-- Vídeos órfãos (nenhuma estratégia atual os usa)
SELECT id, file_path, file_size, created_at
  FROM videos v
 WHERE NOT EXISTS (SELECT 1 FROM strategies s WHERE s.video_id = v.id)
 ORDER BY created_at;

-- -----------------------------------------------------------------------------
-- Depois de confirmar que os objetos já saíram do Storage (passo 1 acima),
-- descomente e rode para limpar as linhas do banco:
--
-- DELETE FROM images i
--  WHERE NOT EXISTS (SELECT 1 FROM strategies s WHERE s.cover_image_id = i.id)
--    AND NOT EXISTS (SELECT 1 FROM users u WHERE u.avatar LIKE '%' || i.file_path);
--
-- DELETE FROM videos v
--  WHERE NOT EXISTS (SELECT 1 FROM strategies s WHERE s.video_id = v.id);
