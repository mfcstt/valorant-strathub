<?php

declare(strict_types=1);

/**
 * Página de erro HTTP.
 *
 * Inclui o mesmo cabeçalho das páginas internas: sem ele, quem já está logado
 * e cai numa URL inválida perdia o acesso direto a Perfil, Minhas estratégias
 * e Favoritas, restando só o botão "Explorar estratégias".
 *
 * @var int    $code
 * @var string $message
 */

require __DIR__ . '/partials/_header.php';

$titles = [
    403 => 'Acesso negado',
    404 => 'Página não encontrada',
    405 => 'Método não permitido',
    419 => 'Sessão expirada',
    500 => 'Erro interno',
];

$title = $titles[$code] ?? 'Algo deu errado';
?>

<div class="min-h-[70vh] flex flex-col items-center justify-center gap-4 px-6 text-center text-white">
  <p class="font-rammetto text-7xl md:text-9xl text-red-light"><?= e($code) ?></p>

  <h1 class="font-rajdhani text-3xl font-bold text-gray-7"><?= e($title) ?></h1>

  <?php if ($message !== ''): ?>
    <p class="max-w-md text-lg text-gray-6 font-nunito leading-[160%]"><?= e($message) ?></p>
  <?php endif; ?>

  <div class="flex flex-wrap items-center justify-center gap-4 mt-4">
    <a href="/explore"
      class="flex items-center gap-2 bg-red-base uppercase font-medium px-5 py-3 rounded-md text-white font-nunito outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all">
      <i class="ph ph-target text-xl" aria-hidden="true"></i>
      Explorar estratégias
    </a>

    <button type="button" data-history-back
      class="flex items-center gap-2 px-5 py-3 rounded-md text-gray-5 font-nunito bg-gray-1/80 border border-gray-3 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">
      <i class="ph ph-caret-left text-xl" aria-hidden="true"></i>
      Voltar
    </button>
  </div>
</div>
