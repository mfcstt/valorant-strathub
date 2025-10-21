<div class="px-4 md:px-8 lg:px-16 xl:px-24 pt-16 pb-5"> 
  <form class="w-full flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8" method="post" novalidate>
    <h1 class="font-rammetto text-2xl text-[#E5E2E9]">Explorar Estratégias</h1>

    <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
      <div class="flex-1 sm:flex-none">
        <?php input('text', 'pesquisar', 'Pesquisar', 'ph ph-magnifying-glass'); ?>
      </div>

      <?php 
        $orderEmojiMap = [
          'mais_estrelas' => '🌟',
          'menos_estrelas' => '⭐️',
          'mais_avaliadas' => '👥',
          'recentes' => '🕒',
        ];
        $orderEmoji = $orderEmojiMap[$order ?? 'mais_estrelas'] ?? '🌟';
      ?>
      <div class="sm:w-56 relative flex items-center">
        <select name="ordenar" class="inpForm" onchange="this.form.submit()">
          <option value="mais_estrelas" <?= ($order ?? '') === 'mais_estrelas' ? 'selected' : '' ?>>Mais estrelas</option>
          <option value="menos_estrelas" <?= ($order ?? '') === 'menos_estrelas' ? 'selected' : '' ?>>Menos estrelas</option>
          <option value="mais_avaliadas" <?= ($order ?? '') === 'mais_avaliadas' ? 'selected' : '' ?>>Mais avaliadas</option>
          <option value="recentes" <?= ($order ?? '') === 'recentes' ? 'selected' : '' ?>>Recentes</option>
        </select>
        <span class="text-xl absolute left-4 top-1/2 transform -translate-y-1/2 pointer-events-none text-gray-5" title="Ordenação selecionada"><?= $orderEmoji ?></span>
      </div>

      <a href="/strategy-create" class="flex items-center justify-center gap-2 px-5 py-3 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
        <i class="ph ph-plus text-xl"></i>
        Novo
      </a>
    </div>
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
</div>