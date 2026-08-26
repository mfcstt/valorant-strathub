<?php

declare(strict_types=1);

use App\Support\Auth;

/**
 * Card de estratégia usado em Explorar, Minhas estratégias e Favoritas.
 *
 * ## Estrutura
 *
 * A versão anterior era um `<a>` com `<form>` e `<button>` dentro — conteúdo
 * interativo aninhado em âncora é HTML inválido, e o comportamento varia por
 * navegador (daí os `event.stopPropagation()` inline tentando remendar). Aqui o
 * card é um `<article>`; o link que cobre a área fica numa camada abaixo dos
 * botões de ação, que passam a ser irmãos e não descendentes dele.
 */

/** @var \App\Models\Strategy $strategy */

$isOwner = Auth::check() && (int) $strategy->user_id === Auth::id();
$detailUrl = '/strategy?id=' . (int) $strategy->id;
$currentUrl = '/' . ltrim((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/explore', PHP_URL_PATH) ?? ''), '/');
$queryString = query_string();
$returnTo = $queryString !== '' ? $currentUrl . '?' . $queryString : $currentUrl;
?>

<article
  class="estrategiaCard group relative w-full h-[280px] rounded-xl overflow-hidden focus-within:outline focus-within:outline-2 focus-within:outline-red-base transition-all ease-in-out duration-300 hover:scale-[1.02] hover:shadow-2xl hover:shadow-red-base/20">

  <!-- Mídia -->
  <div class="absolute inset-0 overflow-hidden rounded-xl">
    <?php if ($strategy->cover_image_url): ?>
      <img src="<?= e($strategy->cover_image_url) ?>" alt="" loading="lazy" decoding="async"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-[400ms]">
    <?php elseif ($strategy->video_url): ?>
      <video data-src="<?= e($strategy->video_url) ?>" preload="none" muted playsinline
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-[400ms] video-cover lazy-video"></video>
      <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <span class="flex items-center justify-center bg-black/45 border border-white/40 rounded-full w-14 h-14">
          <i class="ph-fill ph-play text-white text-2xl ml-0.5" aria-hidden="true"></i>
        </span>
      </div>
    <?php else: ?>
      <div class="w-full h-full bg-gray-2 flex items-center justify-center">
        <span class="text-gray-5 text-sm font-nunito">Sem mídia</span>
      </div>
    <?php endif; ?>
  </div>

  <!-- Gradiente e borda -->
  <div class="shadingCard absolute inset-0 z-[1] rounded-xl pointer-events-none"></div>
  <div class="absolute inset-0 z-[2] border-2 border-gray-2 rounded-lg pointer-events-none"></div>

  <!-- Link que cobre o card. Fica abaixo dos botões na ordem de empilhamento,
       então cliques nos botões não passam por ele. -->
  <a href="<?= e($detailUrl) ?>" class="absolute inset-0 z-[3] outline-none">
    <span class="sr-only">Abrir estratégia <?= e($strategy->title) ?></span>
  </a>

  <!-- Conteúdo -->
  <div class="relative z-[4] h-full flex flex-col justify-between p-4 pointer-events-none">
    <div class="flex items-start gap-2">
      <p class="flex items-center gap-1.5 px-3 py-1.5 text-lg text-gray-7 font-bold font-rajdhani bg-gray-1/80 rounded-full backdrop-blur-sm">
        <?= e(number_format($strategy->ratingAverage(), 1, ',', '.')) ?>
        <span class="text-xs font-medium">/ 5</span>
        <span class="text-xs font-medium">
          • <?= e($strategy->ratingsCount()) ?>
          <?= $strategy->ratingsCount() === 1 ? 'avaliação' : 'avaliações' ?>
        </span>
        <i class="ph-fill ph-star text-sm" aria-hidden="true"></i>
      </p>

      <?php if ($strategy->isPending()): ?>
        <p class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold font-rajdhani text-gray-6 bg-gray-1/80 rounded-full backdrop-blur-sm uppercase">
          <i class="ph ph-clock" aria-hidden="true"></i>
          Em análise
        </p>
      <?php elseif ($strategy->isRejected()): ?>
        <p class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold font-rajdhani text-error-light bg-gray-1/80 rounded-full backdrop-blur-sm uppercase">
          <i class="ph ph-x-circle" aria-hidden="true"></i>
          Rejeitada
        </p>
      <?php endif; ?>
    </div>

    <div class="space-y-3">
      <h2 class="text-gray-7 text-2xl font-bold font-rajdhani leading-tight break-words line-clamp-2">
        <?= e($strategy->title) ?>
      </h2>

      <div class="flex items-center gap-2 text-gray-5 text-sm font-nunito">
        <i class="ph ph-user-focus text-base" aria-hidden="true"></i>
        <span><?= e($strategy->agent_name ?? 'Agente não definido') ?></span>
      </div>

      <div class="flex items-center gap-2 text-gray-5 text-sm font-nunito">
        <i class="ph ph-map-trifold text-base" aria-hidden="true"></i>
        <span><?= e($strategy->map_name ?? 'Mapa não definido') ?></span>
        <span class="ml-1 px-2 py-0.5 bg-red-base/80 text-white text-[10px] font-bold rounded-full backdrop-blur-sm uppercase">
          <?= e($strategy->category) ?>
        </span>
      </div>

      <p class="text-gray-6 text-sm leading-relaxed font-nunito line-clamp-2">
        <?= e($strategy->description) ?>
      </p>
    </div>
  </div>

  <!-- Ações: agrupadas no topo para não cobrirem o texto do card -->
  <?php if (Auth::check()): ?>
    <div class="absolute z-[5] top-3 right-3 flex items-center gap-2">
      <form method="post" action="/favorite-toggle">
        <?= csrf_field() ?>
        <input type="hidden" name="strategy_id" value="<?= e($strategy->id) ?>">
        <input type="hidden" name="redirect" value="<?= e($returnTo) ?>">

        <button type="submit"
          class="flex px-3 py-1.5 bg-gray-1/80 backdrop-blur-sm border border-gray-3 rounded-md text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all ease-in-out duration-300"
          aria-label="<?= $strategy->isFavorite() ? 'Remover das favoritas' : 'Adicionar às favoritas' ?>">
          <i class="<?= $strategy->isFavorite() ? 'ph-fill ph-heart text-red-light' : 'ph ph-heart' ?> text-base"
            aria-hidden="true"></i>
        </button>
      </form>

      <?php if ($isOwner): ?>
        <a href="/strategy-edit?id=<?= e($strategy->id) ?>"
          class="flex px-3 py-1.5 bg-gray-1/80 backdrop-blur-sm border border-gray-3 rounded-md text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all ease-in-out duration-300"
          aria-label="Editar estratégia">
          <i class="ph ph-pencil-simple text-base" aria-hidden="true"></i>
        </a>

        <form method="post" action="/strategy-delete"
          data-confirm="Excluir “<?= e($strategy->title) ?>”? Esta ação é irreversível.">
          <?= csrf_field() ?>
          <input type="hidden" name="strategy_id" value="<?= e($strategy->id) ?>">

          <button type="submit"
            class="flex px-3 py-1.5 bg-gray-1/80 backdrop-blur-sm border border-gray-3 rounded-md text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all ease-in-out duration-300"
            aria-label="Excluir estratégia">
            <i class="ph ph-trash text-base" aria-hidden="true"></i>
          </button>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</article>
