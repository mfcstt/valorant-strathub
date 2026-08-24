<?php

declare(strict_types=1);

/**
 * Front controller.
 *
 * Além de delegar para a aplicação, este arquivo serve os arquivos estáticos
 * quando a plataforma de hospedagem manda toda requisição para o PHP (é o caso
 * da Vercel, que reescreve tudo para `?path=...`). Servidores com raiz em
 * `public/` entregam os estáticos antes de chegar aqui e o bloco abaixo nem roda.
 */

require __DIR__ . '/static.php';

serve_static_asset(__DIR__);

require dirname(__DIR__) . '/src/bootstrap.php';
