<?php

declare(strict_types=1);

/**
 * Fila de moderação: só quem é admin (App\Support\Auth::isAdmin()) chega aqui
 * — o controller já bloqueia o resto com 403.
 *
 * @var list<\App\Models\Strategy> $strategies
 * @var int $total
 * @var int $page
 * @var int $total_pages
 */
?>

<div class="px-4 md:px-8 lg:px-16 xl:px-24 pt-4 pb-8">
  <header class="mb-8">
    <h1 class="text-gray-7 font-rammetto text-2xl">Moderação</h1>
    <p class="mt-1 text-sm text-gray-5 font-nunito">
      <?= e($total) ?> <?= $total === 1 ? 'estratégia aguardando' : 'estratégias aguardando' ?> revisão.
    </p>
  </header>

  <?php if ($strategies === []): ?>
    <div class="flex flex-col gap-4 items-center text-center font-nunito py-16">
      <i class="ph ph-check-circle text-gray-4 text-[44px]" aria-hidden="true"></i>
      <p class="max-w-sm text-gray-7 text-lg">Fila vazia</p>
      <p class="max-w-sm text-gray-6 leading-[160%]">Nada esperando revisão no momento.</p>
    </div>
  <?php else: ?>
    <section class="flex flex-col gap-6 max-w-4xl mx-auto">
      <?php foreach ($strategies as $strategy): ?>
        <article class="rounded-[18px] bg-gray-2 border border-gray-3 p-5 md:p-6">
          <div class="flex flex-col md:flex-row gap-5">
            <!-- Mídia -->
            <div class="w-full md:w-48 h-32 shrink-0 rounded-md overflow-hidden bg-gray-3">
              <?php if ($strategy->cover_image_url): ?>
                <img src="<?= e((string) $strategy->cover_image_url) ?>" alt="" loading="lazy"
                  class="w-full h-full object-cover">
              <?php elseif ($strategy->video_url): ?>
                <video src="<?= e((string) $strategy->video_url) ?>" muted playsinline preload="metadata"
                  class="w-full h-full object-cover"></video>
              <?php else: ?>
                <div class="w-full h-full flex items-center justify-center">
                  <span class="text-gray-5 text-xs font-nunito">Sem mídia</span>
                </div>
              <?php endif; ?>
            </div>

            <!-- Dados -->
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-gray-7 text-lg font-bold font-rajdhani break-words">
                  <a href="/strategy?id=<?= e($strategy->id) ?>" class="hover:text-red-light focus:text-red-light outline-none transition-all">
                    <?= e($strategy->title) ?>
                  </a>
                </h2>
                <span class="px-2 py-0.5 bg-red-base/80 text-white text-[10px] font-bold rounded-full uppercase">
                  <?= e($strategy->category) ?>
                </span>
              </div>

              <p class="mt-1 text-gray-5 text-sm font-nunito">
                Por <?= e($strategy->author_name ?? 'desconhecido') ?>
                • <?= e($strategy->agent_name ?? 'agente não definido') ?>
                • <?= e($strategy->map_name ?? 'mapa não definido') ?>
              </p>

              <p class="mt-3 text-gray-6 text-sm leading-relaxed font-nunito line-clamp-3">
                <?= e($strategy->description) ?>
              </p>

              <!-- Ações -->
              <div class="mt-4 flex flex-col sm:flex-row gap-3">
                <form method="post" action="/admin/moderar">
                  <?= csrf_field() ?>
                  <input type="hidden" name="strategy_id" value="<?= e($strategy->id) ?>">
                  <input type="hidden" name="action" value="approve">
                  <button type="submit"
                    class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all">
                    <i class="ph ph-check text-lg" aria-hidden="true"></i>
                    Aprovar
                  </button>
                </form>

                <details class="w-full sm:w-auto">
                  <summary
                    class="list-none cursor-pointer w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 rounded-md text-gray-5 font-nunito bg-gray-1/80 border border-gray-3 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">
                    <i class="ph ph-x text-lg" aria-hidden="true"></i>
                    Rejeitar
                  </summary>

                  <form method="post" action="/admin/moderar" class="mt-3 flex flex-col gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="strategy_id" value="<?= e($strategy->id) ?>">
                    <input type="hidden" name="action" value="reject">

                    <label for="nota-<?= e($strategy->id) ?>" class="sr-only">Motivo da rejeição</label>
                    <textarea id="nota-<?= e($strategy->id) ?>" name="nota" required maxlength="500" rows="2"
                      placeholder="Explique o motivo — a pessoa vai ver isso e pode corrigir e reenviar"
                      class="w-full resize-none bg-gray-1 border border-gray-3 rounded-md px-3 py-2 text-gray-7 font-nunito text-sm leading-6 placeholder:text-gray-5 outline-none focus:outline-red-base"></textarea>

                    <button type="submit"
                      class="self-start flex items-center gap-2 px-4 py-2 rounded-md text-white font-nunito bg-error-base outline-none hover:bg-error-light focus:bg-error-light focus:outline-red-base transition-all">
                      <i class="ph ph-paper-plane-tilt text-lg" aria-hidden="true"></i>
                      Confirmar rejeição
                    </button>
                  </form>
                </details>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </section>

    <?php if ($total_pages > 1): ?>
      <nav class="mt-8 flex items-center justify-center gap-3" aria-label="Paginação">
        <?php if ($page > 1): ?>
          <a href="/admin/moderacao?page=<?= e($page - 1) ?>"
            class="px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">
            Anterior
          </a>
        <?php else: ?>
          <span class="px-4 py-2 rounded-md border border-gray-2 text-gray-4 cursor-not-allowed">Anterior</span>
        <?php endif; ?>

        <span class="px-3 py-2 text-gray-6 font-nunito">Página <?= e($page) ?> de <?= e($total_pages) ?></span>

        <?php if ($page < $total_pages): ?>
          <a href="/admin/moderacao?page=<?= e($page + 1) ?>"
            class="px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">
            Próxima
          </a>
        <?php else: ?>
          <span class="px-4 py-2 rounded-md border border-gray-2 text-gray-4 cursor-not-allowed">Próxima</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>
