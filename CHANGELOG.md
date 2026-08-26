# Changelog

Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/).

## [2.0.0] - 2026-08-24

Revisão completa do projeto depois da entrega acadêmica: segurança, correção de
defeitos, reorganização e ferramentas. Sem mudança no que a aplicação faz - o
conjunto de funcionalidades é o mesmo da versão entregue.

### Segurança

- **Bypass de autenticação corrigido.** A sessão era reidratada por um cookie
  `auth_uid` acompanhado do HMAC do próprio id, com o segredo tendo um valor
  padrão embutido no código-fonte. Qualquer pessoa que lesse o repositório podia
  calcular a assinatura de um id e entrar como aquele usuário. Substituído por
  token no padrão split-token, com validator guardado apenas como hash SHA-256,
  expiração conferida no servidor e revogação por dispositivo.
- **`.env` removido do versionamento.** O arquivo estava commitado num
  repositório público com a senha do Postgres e a `SUPABASE_SERVICE_KEY`, que
  ignora RLS. O `.gitignore` passou a ignorá-lo - o README afirmava que já
  ignorava.
- **Proteção CSRF adicionada.** Não existia nenhuma. Synchronizer token
  verificado num ponto único do roteador para toda requisição `POST`.
- **XSS armazenado corrigido.** Título e descrição de estratégia eram impressos
  sem escape, disparando na página inicial para todos os visitantes. Toda
  interpolação de view passa pelo helper `e()`.
- **Roteamento com allowlist.** O caminho do controller vinha da URL
  (`require "controllers/{$path}.controller.php"`), deixando o filesystem
  navegável pela query string.
- **Upload valida o conteúdo.** A verificação usava `$_FILES['type']`, que é o
  header declarado pelo cliente. Agora o tipo é medido com `finfo`, o
  `is_uploaded_file` é conferido e o nome de destino é gerado, nunca derivado do
  nome original.
- **Fixação de sessão.** `session_regenerate_id` no login e
  `session.use_strict_mode` habilitado.
- **Open redirect.** O campo `redirect` do formulário de favoritos aceitava
  destino externo.
- **Logout por POST.** Um `GET /logout` podia ser disparado por qualquer site de
  terceiros.
- **Vazamento de informação.** Mensagens de exceção do PDO eram exibidas ao
  usuário; agora vão apenas para o log.
- **Injeção de SQL em identificadores.** `Validation::unique` interpolava tabela
  e coluna na consulta, e a cláusula de ordenação vinha da query string. Ambos
  restritos a allowlists.
- **Cabeçalhos de segurança.** `X-Content-Type-Options`, `X-Frame-Options`,
  `Referrer-Policy` e `Permissions-Policy`; cookie de sessão `HttpOnly` e
  `SameSite=Lax`.

### Correções

- **Ordenação das listagens.** A consulta paginava por data e o PHP reordenava
  depois, com `usort`, apenas os itens da página atual. A estratégia mais bem
  avaliada do site podia nunca aparecer no topo de "Mais estrelas". A ordenação
  passou para o `ORDER BY`.
- **Flash messages nunca eram consumidas.** `Flash::get()` não removia a chave da
  sessão, então dados de formulário reapareciam nas navegações seguintes.
- **Erro 500 em estratégia inexistente.** `/strategy?id=999` acessava propriedade
  de `null`; agora retorna 404.
- **N+1 nas listagens.** `is_favorite` era uma consulta por card, e cada model
  abria a sua própria conexão PDO - mais de quinze por página. Conexão única por
  requisição e subconsulta `EXISTS`.
- **Fotos de Brimstone e Cypher.** O banco apontava para `brimstone.png` e
  `cypher.png`, mas os arquivos em disco eram `brim.png` e `chypher.png`. As duas
  imagens estavam quebradas desde a entrega.
- **Busca com `%` ou `_`.** Os curingas do `LIKE` não eram escapados, então
  procurar por "100%" trazia a tabela inteira.
- **HTML inválido nos cards.** `<form>` e `<button>` estavam aninhados dentro de
  um `<a>`, com `event.stopPropagation()` inline tentando compensar.
- **Duração do vídeo** era exibida em segundos crus.
- **Classes `duração-300`** (com acento) não existiam no Tailwind e não aplicavam
  transição nenhuma.
- **Seleção de agente e mapa** tinha duas implementações concorrentes, uma no
  arquivo de scripts e outra inline no componente, disputando o mesmo elemento.
- **Nota de avaliação** não era validada na faixa de 1 a 5 pela aplicação; a
  constraint do banco gerava erro não tratado.
- **Avaliar a própria estratégia** era permitido, inflando a nota do autor e
  distorcendo a ordenação da página inicial.

### Estrutura

- Autoload PSR-4 (`App\`) no lugar de nove `require` manuais no front controller.
- `Flash`, `Validation`, `database`, `functions` e `config` saíram da raiz do
  projeto para `src/Support`.
- `Estrategia` renomeada para `Strategy`; a tabela `estrategias` e a coluna
  `estrategia_id` acompanharam, com migração em `database/migrations/`.
- Removidos os resquícios de um projeto anterior de catálogo de filmes: a view
  `movie.component`, o partial morto `_cardMovie`, a coluna `rated_movies`, a
  regra de validação `ano_de_lancamento` e a mensagem "Livros do futuro são
  inválidos".
- `Image` e `Video`, praticamente idênticas, passaram a herdar de `MediaFile`.
- Explorar, Minhas estratégias e Favoritas compartilham um único partial e um
  único parser de filtros, no lugar de três cópias que já divergiam entre si.
- Rota `/myStrategy` renomeada para `/my-strategies`, com redirecionamento 301
  da antiga.
- Removidos 24 scripts descartáveis de depuração e teste da pasta `scripts/`.

### Adicionado

- 74 testes com PHPUnit, usando SQLite temporário por caso de teste.
- PHPStan nível 6, sem erros.
- Pipeline de CI: suíte em PHP 8.1 e 8.3, compilação de CSS e verificação de que
  a imagem Docker responde.
- Driver de armazenamento local, para o projeto rodar sem conta no Supabase.
- Seeder de demonstração (`database/seed-demo.php`).
- `LICENSE` (MIT) com nota sobre os ativos da Riot Games.
- `docker-compose.yml`, `.dockerignore` e `.editorconfig`.

### Alterado

- Tailwind compilado (24 KB) no lugar do CDN que compilava no navegador.
- Ícones Phosphor servidos do próprio domínio, em vez de um `<script>` de CDN sem
  versão fixa.
- Imagem Docker reescrita: build em três estágios, Apache com `DocumentRoot` em
  `public/` no lugar de `php -S`, e sem o `composer install || true` que engolia
  falha de instalação.
- Blocos `<script>` inline movidos para arquivos, viabilizando cache e, mais
  adiante, uma Content Security Policy sem `unsafe-inline`.
- Acessibilidade: `label` associado a cada campo, `aria-label` nos botões de
  ícone, skip link e foco preso dentro do modal.

## [1.0.0] - 2025

Versão entregue como trabalho de conclusão de curso.
