<?php $formData = $_SESSION["flash_formData"]["pesquisar"] ?? null; ?>

<div class="px-4 md:px-8 lg:px-16 xl:px-24 pt-16 pb-5">
  <form class="w-full flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8" method="post" novalidate>
    <h1 class="font-rammetto text-2xl text-[#E5E2E9]">Minhas estratégias</h1>

    <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
      <div class="flex-1 sm:flex-none">
        <?php input('text', 'pesquisar', 'Pesquisar', 'ph ph-magnifying-glass'); ?>
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

  <?php if (!$estrategias && $formData): ?>
    <div class="flex flex-col gap-5 items-center text-center font-nunito">
      <i class="ph ph-target text-gray-4 text-[44px]"></i>

      <p class="w-80 text-gray-6 leading-[160%]">
        Nenhuma estratégia encontrada com "<?= $search ?>" </br>
        Que tal tentar outra busca?
      </p>

      <a href="/myStrategy" class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
        <i class="ph ph-x text-xl"></i> Limpar filtro
      </a>
    </div>
  <?php elseif (!$estrategias): ?>
    <div class="flex flex-col gap-5 items-center text-center font-nunito">
      <i class="ph ph-target text-gray-4 text-[44px]"></i>

      <p class="text-gray-6 leading-[160%]">
        Nenhuma estratégia registrada. </br>
        Que tal começar cadastrando sua primeira estratégia?
      </p>

      <a href="/strategy-create" class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
        <i class="ph ph-plus text-xl"></i> Cadastrar novo
      </a>
    </div>
  <?php endif; ?>  
</div>