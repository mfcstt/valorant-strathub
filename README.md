# Valorant StratHub

Aplicação PHP simples para cadastro e exploração de estratégias de Valorant, com armazenamento de imagens no Supabase Storage.

## Requisitos
- PHP 8.x
- Composer
- Supabase (projeto com Postgres e Storage)

## Setup
1. Copie o arquivo `.env.example` para `.env` e preencha com suas credenciais:
   - `SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_SERVICE_KEY`
   - Para banco de dados, escolha uma opção:
     - `USE_SQLITE=true` para desenvolvimento local com SQLite
     - `USE_SQLITE=false` e configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` para Postgres
2. Instale dependências:
   ```bash
   composer install
   ```
3. Se estiver usando SQLite, o arquivo `public/database.sqlite` será criado automaticamente quando necessário.
4. Se estiver usando Postgres, execute os scripts de migração conforme o guia.

## Rodando localmente
- Servidor embutido do PHP:
  ```bash
  php -S localhost:8000 -t public
  ```
  Acesse `http://localhost:8000`.

## Migrações
- `migrate_supabase.php`: cria tabelas e índices no Postgres.
- `migrate_images_to_supabase.php`: migra imagens locais para o Supabase Storage e associa à tabela `images`.
- Consulte `MIGRATION_GUIDE.md` para detalhes.

## Segurança
- Nunca faça commit de `.env` ou segredos. O repositório contém `.env.example` com placeholders.
- `.gitignore` foi configurado para ignorar `.env`, `vendor/`, `public/database.sqlite` e artefatos locais.

## Estrutura
- `src/models`: modelos (Estrategia, Image, etc.)
- `src/services`: serviços (SupabaseStorageService)
- `src/controllers`: controladores das rotas
- `src/views`: templates e componentes
- `public`: assets, index e servidor local

## Suporte
Problemas ou dúvidas? Abra uma issue no GitHub após subir o repositório.