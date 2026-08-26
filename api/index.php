<?php

declare(strict_types=1);

/**
 * Ponto de entrada exigido pela Vercel para o runtime vercel-php.
 *
 * A plataforma só reconhece Serverless Functions dentro de `api/` - não é
 * possível apontar `functions` do vercel.json para outro diretório. Este
 * arquivo existe só por essa exigência; toda a lógica real continua em
 * public/index.php, que também serve como front controller local (Apache,
 * `php -S`, Docker). __DIR__ dentro do arquivo incluído resolve para a pasta
 * dele mesmo (public/), não para api/, então nenhum caminho relativo quebra.
 */
require dirname(__DIR__) . '/public/index.php';
