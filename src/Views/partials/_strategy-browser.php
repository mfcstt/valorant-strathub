<?php

declare(strict_types=1);

use App\Models\Strategy;

/**
 * Navegador de estratégias: busca, ordenação, filtros, grade e paginação.
 *
 * Explorar, Minhas estratégias e Favoritas eram três arquivos de ~140 linhas com
 * o mesmo markup copiado — e as cópias já divergiam (rótulos, ícones e ids de
 * painel diferentes para o mesmo controle). Agora as três páginas passam por aqui
 * e informam apenas o que muda de fato.
 *
 * Variáveis esperadas do componente:
 *
 * @var string      $browser_route         rota da página, para links e paginação
 * @var string      $browser_title         título exibido
 * @var string      $browser_empty_title   título do estado vazio
 * @var string      $browser_empty_message texto do estado vazio
 * @var string      $browser_empty_icon    ícone do estado vazio
 * @var bool        $browser_show_create   mostra o botão "Nova estratégia"
 *
 * E, vindas de App\Http\StrategyListing:
 *
 * @var list<Strategy> $strategies
 * @var int         $total
 * @var int         $page
 * @var int         $total_pages
 * @var string      $search
 * @var string      $order
 * @var list<mixed> $agents
 * @var list<mixed> $maps
 * @var list<string> $categories
 * @var int|null    $filter_agent
 * @var int|null    $filter_map
 * @var string|null $filter_category
 * @var bool        $has_active_filters
 */

$orderLabels = [
    'mais_estrelas' => ['Mais estrelas', 'ph-fill ph-star'],
    'menos_estrelas' => ['Menos estrelas', 'ph ph-star'],
    'mais_avaliadas' => ['Mais avaliadas', 'ph ph-users-three'],
    'recentes' => ['Mais recentes', 'ph ph-clock'],
    'antigas' => ['Mais antigas', 'ph ph-clock-counter-clockwise'],
];

$orderIcon = $orderLabels[$order][1] ?? 'ph-fill ph-star';
$isFiltered = $search !== '' || $has_active_filters;
?>

<div class="px-4 md:px-8 lg:px-16 xl:px-24 pt-4 pb-8">
  <form class="w-full flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8"
    method="get" action="<?= e($browser_route) ?>" novalidate data-strategy-filters>

    <div>
      <h1 class="font-rammetto text-2xl text-gray-7"><?= e($browser_title) ?></h1>
      <?php if ($total > 0): ?>
        <?php
        // As duas palavras concordam em número juntas — "1 estratégia
        // publicadas" (singular seguido de plural) já apareceu em produção.
        $isSingular = $total === 1;
        $noun = $isSingular ? 'estratégia' : 'estratégias';
        $participle = $isFiltered
            ? ($isSingular ? 'encontrada' : 'encontradas')
            : ($isSingular ? 'publicada' : 'publicadas');
        ?>
        <p class="mt-1 text-sm text-gray-5 font-nunito">
          <?= e($total) ?> <?= $noun ?> <?= $participle ?>
        </p>
      <?php endif; ?>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto relative">
      <div class="flex-1 sm:flex-none">
        <?php input('search', 'pesquisar', 'Pesquisar', 'ph ph-magnifying-glass'); ?>
      </div>

      <div class="sm:w-56 relative flex items-center">
        <i class="<?= e($orderIcon) ?> text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
          aria-hidden="true"></i>
        <label for="ordenar" class="sr-only">Ordenar por</label>
        <select id="ordenar" name="ordenar" class="inpForm pl-10" data-submit-on-change>
          <?php foreach (Strategy::orderOptions() as $option): ?>
            <option value="<?= e($option) ?>" <?= $order === $option ? 'selected' : '' ?>>
              <?= e($orderLabels[$option][0] ?? $option) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="button" data-filters-toggle aria-expanded="false" aria-controls="filtersPanel"
        class="flex items-center justify-center gap-2 px-5 py-3 rounded-md font-nunito bg-gray-1/80 border <?= $has_active_filters ? 'border-red-base text-red-light' : 'border-gray-3 text-gray-5' ?> outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all ease-in-out duration-300">
        <i class="<?= $has_active_filters ? 'ph-fill' : 'ph' ?> ph-funnel-simple text-xl" aria-hidden="true"></i>
        Filtrar
      </button>

      <?php if ($browser_show_create): ?>
        <a href="/strategy-create"
          class="flex items-center justify-center gap-2 px-5 py-3 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
          <i class="ph ph-plus text-xl" aria-hidden="true"></i>
          Nova
        </a>
      <?php endif; ?>

      <div id="filtersPanel" data-filters-panel
        class="hidden absolute top-full right-0 z-50 w-[92vw] max-w-md mt-2 p-4 rounded-md bg-gray-3 border border-gray-4 shadow-xl">
        <div class="flex flex-col gap-4">
          <div class="w-full">
            <label for="filtro_categoria" class="block text-gray-7 font-nunito text-sm mb-2">Por categoria</label>
            <div class="relative flex items-center">
              <i class="ph ph-tag text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
                aria-hidden="true"></i>
              <select id="filtro_categoria" name="filtro_categoria" class="inpForm pl-10 w-full">
                <option value="">Todas as categorias</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?= e($category) ?>" <?= $filter_category === $category ? 'selected' : '' ?>>
                    <?= e(ucfirst($category)) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="w-full">
            <label for="filtro_agente" class="block text-gray-7 font-nunito text-sm mb-2">Por agente</label>
            <div class="relative flex items-center">
              <i class="ph ph-user-focus text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
                aria-hidden="true"></i>
              <select id="filtro_agente" name="filtro_agente" class="inpForm pl-10 w-full">
                <option value="">Todos os agentes</option>
                <?php foreach ($agents as $agent): ?>
                  <option value="<?= e($agent->id) ?>" <?= $filter_agent === (int) $agent->id ? 'selected' : '' ?>>
                    <?= e($agent->name) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="w-full">
            <label for="filtro_mapa" class="block text-gray-7 font-nunito text-sm mb-2">Por mapa</label>
            <div class="relative flex items-center">
              <i class="ph ph-map-trifold text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
                aria-hidden="true"></i>
              <select id="filtro_mapa" name="filtro_mapa" class="inpForm pl-10 w-full">
                <option value="">Todos os mapas</option>
                <?php foreach ($maps as $map): ?>
                  <option value="<?= e($map->id) ?>" <?= $filter_map === (int) $map->id ? 'selected' : '' ?>>
                    <?= e($map->name) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
              class="flex items-center gap-2 px-5 py-3 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
              <i class="ph ph-check text-xl" aria-hidden="true"></i>
              Aplicar
            </button>
            <a href="<?= e($browser_route) ?>"
              class="flex items-center gap-2 px-5 py-3 rounded-md text-gray-5 font-nunito bg-gray-1/80 border border-gray-3 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all ease-in-out duration-300">
              <i class="ph ph-x text-xl" aria-hidden="true"></i>
              Limpar
            </a>
          </div>
        </div>
      </div>
    </div>

    <div data-filters-backdrop class="hidden fixed inset-0 z-40"></div>
  </form>

  <?php if ($strategies !== []): ?>
    <section class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-7xl mx-auto">
      <?php foreach ($strategies as $strategy): ?>
        <?php strategy_card($strategy); ?>
      <?php endforeach; ?>
    </section>

    <?php if ($total_pages > 1): ?>
      <nav class="mt-8 flex items-center justify-center gap-3" aria-label="Paginação">
        <?php if ($page > 1): ?>
          <a href="<?= e($browser_route . '?' . query_string(['page' => $page - 1])) ?>"
            class="px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">
            Anterior
          </a>
        <?php else: ?>
          <span class="px-4 py-2 rounded-md border border-gray-2 text-gray-4 cursor-not-allowed">Anterior</span>
        <?php endif; ?>

        <span class="px-3 py-2 text-gray-6 font-nunito">
          Página <?= e($page) ?> de <?= e($total_pages) ?>
        </span>

        <?php if ($page < $total_pages): ?>
          <a href="<?= e($browser_route . '?' . query_string(['page' => $page + 1])) ?>"
            class="px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">
            Próxima
          </a>
        <?php else: ?>
          <span class="px-4 py-2 rounded-md border border-gray-2 text-gray-4 cursor-not-allowed">Próxima</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php else: ?>
    <div class="flex flex-col gap-5 items-center text-center font-nunito py-10">
      <i class="<?= e($browser_empty_icon) ?> text-gray-4 text-[44px]" aria-hidden="true"></i>

      <?php if ($isFiltered): ?>
        <p class="max-w-sm text-gray-6 leading-[160%]">
          <?php if ($search !== ''): ?>
            Nenhuma estratégia encontrada para <span class="text-gray-7">“<?= e($search) ?>”</span>.
          <?php else: ?>
            Nenhuma estratégia corresponde aos filtros selecionados.
          <?php endif; ?>
          <br>Que tal ajustar a busca?
        </p>

        <a href="<?= e($browser_route) ?>"
          class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
          <i class="ph ph-x text-xl" aria-hidden="true"></i>
          Limpar filtros
        </a>
      <?php else: ?>
        <p class="max-w-sm text-gray-7 text-lg"><?= e($browser_empty_title) ?></p>
        <p class="max-w-sm text-gray-6 leading-[160%]"><?= e($browser_empty_message) ?></p>

        <?php if ($browser_show_create): ?>
          <a href="/strategy-create"
            class="flex items-center gap-2 px-5 py-3 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all">
            <i class="ph ph-plus text-xl" aria-hidden="true"></i>
            Criar minha primeira estratégia
          </a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
