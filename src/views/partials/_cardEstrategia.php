<a href="/strategy?id=<?= $estrategia->id ?>" class="estrategiaCard group relative w-full h-[280px] rounded-xl outline-none focus:outline-red-base transition-all ease-in-out duration-300 hover:scale-[1.02] hover:shadow-2xl hover:shadow-red-base/20">
  <!-- Borda -->
  <div class="absolute z-[2] w-full h-full border-2 border-gray-2 rounded-lg overflow-hidden"></div>

  <!-- Conteúdo -->
  <article class="shadingCard absolute z-[1] w-full h-full flex flex-col justify-between rounded-xl">
    <!-- Header com rating -->
    <div class="flex justify-between items-start p-4">
      <div class="flex items-center gap-1.5 px-3 py-1.5 text-lg text-gray-7 font-bold font-rajdhani bg-[#0f0f1acc] rounded-full backdrop-blur-sm">
        <p><?= number_format($estrategia->rating_average, 1, ',', '.') ?> <span class="text-xs font-medium">/ 5</span> <span class="text-xs font-medium">• <?= $estrategia->ratings_count ?> <?= $estrategia->ratings_count == 1 ? 'avaliação' : 'avaliações' ?></span></p>
        <i class="ph-fill ph-star text-sm"></i>
      </div>
      
      <!-- Badge de categoria -->
      <div class="px-3 py-1.5 bg-red-base/80 text-white text-xs font-bold font-nunito rounded-full backdrop-blur-sm">
        <?= $estrategia->category ?>
      </div>
    </div>

    <!-- Conteúdo principal -->
    <div class="flex-1 flex flex-col justify-end p-4">
      <div class="space-y-3">
        <h2 class="text-gray-7 text-2xl font-bold font-rajdhani leading-tight"><?= $estrategia->title ?></h2>
        
        <div class="flex items-center gap-2 text-gray-5 text-sm font-nunito">
          <i class="ph ph-user text-base"></i>
          <span><?= $estrategia->agent_name ?? 'Agente não definido' ?></span>
        </div>

        <p class="description text-gray-6 text-sm leading-relaxed font-nunito line-clamp-3">
          <?= $estrategia->description ?>
        </p>
      </div>
    </div>
  </article>

  <!-- Imagem -->
  <div class="absolute w-full h-full overflow-hidden rounded-xl">
    <?php if ($estrategia->cover_image_url): ?>
      <img src="<?= $estrategia->cover_image_url ?>" alt="Capa da estratégia" class="w-full h-full object-cover group-hover:scale-110 group-focus:scale-110 transition-transform duration-[400ms]">
    <?php else: ?>
      <div class="w-full h-full bg-gray-800 flex items-center justify-center">
        <span class="text-gray-500 text-sm">Sem imagem</span>
      </div>
    <?php endif; ?>
  </div>
</a>