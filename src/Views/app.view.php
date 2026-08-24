<?php

declare(strict_types=1);

/**
 * Casca das páginas internas: cabeçalho, navegação e o componente da página.
 *
 * @var string $component
 */
?>

<?php require __DIR__ . '/partials/_header.php'; ?>

<main id="main-content">
  <div class="max-w-[1366px] mx-auto pt-6 md:pt-10">
    <?php require __DIR__ . "/components/{$component}.component.php"; ?>
  </div>
</main>
