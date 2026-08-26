<?php

declare(strict_types=1);

/**
 * Template base de todas as páginas.
 *
 * Recebe $view (nome da view) e, quando houver, $component - ambos já validados
 * por App\Support\View.
 *
 * O CSS do Tailwind agora vem de um arquivo compilado (`/CSS/tailwind.build.css`)
 * em vez do `cdn.tailwindcss.com`. O CDN compila as classes no navegador a cada
 * carregamento e a própria documentação do Tailwind o marca como inadequado para
 * produção: são ~400 KB de JavaScript e um flash de conteúdo sem estilo.
 */

/** @var string $view */
/** @var string|null $component */

$message = flash()->get('message');
$error = flash()->get('error');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Plataforma colaborativa para criar, explorar e avaliar estratégias de Valorant por agente e mapa.">
  <meta name="color-scheme" content="dark">

  <title>Valorant StratHub</title>

  <link rel="icon" type="image/svg+xml" href="/assets/icons/logo.svg">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&family=Rajdhani:wght@300;400;500;600;700&family=Rammetto+One&display=swap">

  <link rel="stylesheet" href="<?= e(asset_version('/CSS/tailwind.build.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset_version('/CSS/global.css')) ?>">

  <!-- Ícones servidos do próprio domínio (npm run assets:vendor), em vez de CDN -->
  <link rel="stylesheet" href="/vendor/phosphor/regular/style.css">
  <link rel="stylesheet" href="/vendor/phosphor/fill/style.css">
</head>

<body class="bg-gray-1 min-h-screen flex flex-col">
  <a href="#main-content"
    class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:top-4 focus:left-4 focus:px-4 focus:py-2 focus:rounded-md focus:bg-red-base focus:text-white">
    Pular para o conteúdo
  </a>

  <div class="relative flex-1 modalOverFlow">
    <?php require __DIR__ . "/../{$view}.view.php"; ?>
  </div>

  <?php require __DIR__ . '/../partials/_footer.php'; ?>

  <?php if (is_string($message) && $message !== ''): ?>
    <div id="message" role="status" aria-live="polite" data-toast
      class="fixed bottom-8 right-[-400px] z-20 w-auto max-w-[90vw] md:max-w-[480px] break-words flex flex-col pb-1 px-1 text-white border border-red-base rounded-md bg-gray-1 shadow-buttonHover">
      <div class="flex items-center gap-2 px-8 pt-4 pb-3">
        <i class="ph ph-check-circle text-red-base text-2xl" aria-hidden="true"></i>
        <span class="text-lg"><?= e($message) ?></span>
      </div>
      <div class="w-full h-0.5 bg-gray-3 rounded-xl">
        <div class="progress h-full bg-red-light" style="width:0"></div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (is_string($error) && $error !== ''): ?>
    <div id="error" role="alert" aria-live="assertive" data-toast
      class="fixed bottom-8 right-[-400px] z-20 w-auto max-w-[90vw] md:max-w-[480px] break-words flex flex-col pb-1 px-1 text-white border border-red-base rounded-md bg-gray-1 shadow-buttonHover">
      <div class="flex items-center gap-2 px-8 pt-4 pb-3">
        <i class="ph ph-warning text-red-base text-2xl" aria-hidden="true"></i>
        <span class="text-lg"><?= e($error) ?></span>
      </div>
      <div class="w-full h-0.5 bg-gray-3 rounded-xl">
        <div class="progress h-full bg-red-light" style="width:0"></div>
      </div>
    </div>
  <?php endif; ?>

  <script src="<?= e(asset_version('/JS/globalScripts.js')) ?>" defer></script>

  <?php
  // Cada view pode ter o seu próprio script. A checagem evita um 404 no console
  // para views que não têm (a página de erro, por exemplo).
  $viewScript = "/JS/{$view}ViewScripts.js";
  if (is_file(dirname(__DIR__, 3) . '/public' . $viewScript)):
      ?>
    <script src="<?= e(asset_version($viewScript)) ?>" defer></script>
  <?php endif; ?>
</body>

</html>
