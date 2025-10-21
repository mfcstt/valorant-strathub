# Guia de Migração e Setup

Este guia cobre o setup local e a migração das imagens do armazenamento local para o Supabase Storage.

## Setup
- Copie `.env.example` para `.env` e preencha:
  - `SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_SERVICE_KEY`
  - Banco de dados:
    - `USE_SQLITE=true` para desenvolvimento com SQLite
    - `USE_SQLITE=false` e configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` para Postgres
- Instale dependências com `composer install`.

## Migração de Banco (Postgres)
- Para criar tabelas e índices no Postgres:
  ```bash
  php migrate_supabase.php
  ```

## Migração de Imagens para Supabase
1. Configure o Supabase Storage:
   - Crie um bucket `strategy-covers` no Storage
   - Políticas: leitura pública (recomendado) ou conforme sua necessidade
2. Execute a migração de imagens locais:
   ```bash
   php migrate_images_to_supabase.php
   ```
   O script irá:
   - Criar a tabela `images` se necessário
   - Adicionar a coluna `cover_image_id` em `estrategias` se necessário
   - Migrar imagens existentes para o bucket
   - Atualizar `estrategias.cover_image_id` com o ID de `images`

## Verificação
- No Supabase Storage, confirme o upload das imagens no bucket
- No app, crie uma estratégia e confirme a exibição da cover

## Limpeza (opcional)
- Após validar a migração:
  - Considere remover a coluna antiga `cover` de `estrategias`:
    ```sql
    ALTER TABLE estrategias DROP COLUMN cover;
    ```
  - Considere excluir `public/assets/images/covers/` (faça backup antes)

## Segurança
- Não faça commit de `.env` ou chaves reais
- Use `.env.example` e `supabase_config_example.txt` como referência
- `config.php` usa valores de `.env`; defaults públicos foram sanitizados

## Troubleshooting
- Upload falha: confira `SUPABASE_URL`, `SUPABASE_ANON_KEY`, políticas do bucket
- Cover quebrada: verifique a URL completa gerada no modelo `Estrategia`
- CORS: veja o console do navegador e políticas de Storage
