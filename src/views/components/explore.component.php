<div class="px-4 md:px-8 lg:px-16 xl:px-24 pt-16 pb-5"> 
  <form class="w-full flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8" method="get" novalidate>
    <h1 class="font-rammetto text-2xl text-[#E5E2E9]">Explorar Estratégias</h1>

    <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto relative">
      <div class="flex-1 sm:flex-none">
        <?php input('text', 'pesquisar', 'Pesquisar', 'ph ph-magnifying-glass'); ?>
      </div>

      <?php 
        $orderIconMap = [
          'mais_estrelas' => 'ph-fill ph-star',
          'menos_estrelas' => 'ph ph-star',
          'mais_avaliadas' => 'ph ph-users',
          'recentes' => 'ph ph-clock',
        ];
        $orderIconClass = $orderIconMap[$order ?? 'mais_estrelas'] ?? 'ph-fill ph-star';
      ?>
      <div class="sm:w-56 relative flex items-center">
        <i class="<?= $orderIconClass ?> text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5" title="Ordenação selecionada"></i>
        <select name="ordenar" class="inpForm pl-10" onchange="this.form.submit()">
          <option value="mais_estrelas" <?= ($order ?? '') === 'mais_estrelas' ? 'selected' : '' ?>>Mais estrelas</option>
          <option value="menos_estrelas" <?= ($order ?? '') === 'menos_estrelas' ? 'selected' : '' ?>>Menos estrelas</option>
          <option value="mais_avaliadas" <?= ($order ?? '') === 'mais_avaliadas' ? 'selected' : '' ?>>Mais avaliadas</option>
          <option value="recentes" <?= ($order ?? '') === 'recentes' ? 'selected' : '' ?>>Recentes</option>
        </select>
      </div>

      <?php $hasActiveFilters = !empty($filter_category ?? '') || !empty($filter_agent ?? '') || !empty($filter_map ?? ''); ?>
      <button type="button" id="toggleFiltersExplore" class="flex items-center justify-center gap-2 px-5 py-3 rounded-md text-white font-nunito bg-gray-1/80 border <?= $hasActiveFilters ? 'border-red-base text-red-light' : 'border-gray-3 text-gray-5' ?> outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all ease-in-out duration-300">
        <i class="<?= $hasActiveFilters ? 'ph-fill ph-funnel-simple' : 'ph ph-funnel-simple' ?> text-xl"></i>
        Filtrar
      </button>

      <a href="/strategy-create" class="flex items-center justify-center gap-2 px-5 py-3 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
        <i class="ph ph-plus text-xl"></i>
        Novo
      </a>

      <div id="filtersPanelExplore" class="hidden absolute top-full right-0 z-50 w-[92vw] max-w-md p-4 rounded-md bg-gray-3 border border-gray-4 shadow-xl">
        <div class="flex flex-col gap-4">
          <div class="w-full">
            <label class="block text-gray-7 font-nunito text-sm mb-2">Por categoria</label>
            <div class="relative flex items-center">
              <i class="ph ph-tag text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5" title="Categoria"></i>
              <select name="filtro_categoria" class="inpForm pl-10 w-full">
                <option value="" <?= empty($filter_category ?? '') ? 'selected' : '' ?>>Todas categorias</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat ?>" <?= ($filter_category ?? '') === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="w-full">
            <label class="block text-gray-7 font-nunito text-sm mb-2">Por agente</label>
            <div class="relative flex items-center">
              <i class="ph ph-user text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5" title="Agente"></i>
              <select name="filtro_agente" class="inpForm pl-10 w-full">
                <option value="" <?= empty($filter_agent ?? '') ? 'selected' : '' ?>>Todos agentes</option>
                <?php foreach ($agents as $agent): ?>
                  <option value="<?= $agent->id ?>" <?= ($filter_agent ?? '') == $agent->id ? 'selected' : '' ?>><?= $agent->name ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="w-full">
            <label class="block text-gray-7 font-nunito text-sm mb-2">Por mapa</label>
            <div class="relative flex items-center">
              <i class="ph ph-map-trifold text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5" title="Mapa"></i>
              <select name="filtro_mapa" class="inpForm pl-10 w-full">
                <option value="" <?= empty($filter_map ?? '') ? 'selected' : '' ?>>Todos mapas</option>
                <?php foreach ($maps as $map): ?>
                  <option value="<?= $map->id ?>" <?= ($filter_map ?? '') == $map->id ? 'selected' : '' ?>><?= $map->name ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="flex items-center gap-2 px-5 py-3 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
              <i class="ph ph-check text-xl"></i>
              Aplicar
            </button>
            <a href="/explore" class="flex items-center gap-2 px-5 py-3 rounded-md text-gray-5 font-nunito bg-gray-1/80 border border-gray-3 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all ease-in-out duration-300">
              <i class="ph ph-x text-xl"></i>
              Limpar
            </a>
          </div>
        </div>
      </div>
    </div>

    <div id="filtersBackdropExplore" class="hidden fixed inset-0 z-40"></div>
  </form>

  <section class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-7xl mx-auto">
    <?php
    foreach ($estrategias as $estrategia) {
      require "../src/views/partials/_cardEstrategia.php";
    }
    ?>

    <!-- Descrição agora usa line-clamp-3 para truncar -->
  </section>
  
  <?php if (!$estrategias): ?>
    <div class="flex flex-col gap-5 items-center text-center font-nunito">
      <i class="ph ph-target text-gray-4 text-[44px]"></i>

      <p class="w-80 text-gray-6 leading-[160%]">
        Nenhuma estratégia encontrada com "<?= $search ?>" </br>
        Que tal tentar outra busca?
      </p>

      <a href="/explore" class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
        <i class="ph ph-x text-xl"></i> Limpar filtro
      </a>
    </div>
  <?php endif; ?>

  <script>
    (function(){
      const btn = document.getElementById('toggleFiltersExplore');
      const panel = document.getElementById('filtersPanelExplore');
      const backdrop = document.getElementById('filtersBackdropExplore');
      if (btn && panel && backdrop) {
        const open = () => { panel.classList.remove('hidden'); backdrop.classList.remove('hidden'); };
        const close = () => { panel.classList.add('hidden'); backdrop.classList.add('hidden'); };
        btn.addEventListener('click', () => panel.classList.contains('hidden') ? open() : close());
        backdrop.addEventListener('click', close);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
      }

      // Fallback: submeter ao limpar busca
      const form = document.querySelector('form');
      const searchInput = form ? form.querySelector('input[name="pesquisar"]') : null;
      const cleanBtn = searchInput ? searchInput.parentElement?.querySelector('.cleanBtn') : null;
      if (form && searchInput && cleanBtn) {
        cleanBtn.addEventListener('click', () => {
          searchInput.value = '';
          form.submit();
        });
      }
    })();
  </script>
</div>