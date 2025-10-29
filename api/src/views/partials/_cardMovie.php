<a href="/strategy?id=<?= $estrategia->id ?>" class="estrategiaCard group relative w-[280px] h-[360px] rounded-xl outline-none focus:outline-red-base transition-all ease-in-out duration-300">
  <!-- Borda -->
  <div class="absolute z-[2] w-full h-full border-2 border-gray-2 rounded-lg overflow-hidden"></div>

  <!-- Conteúdo -->
  <article class="shadingCard absolute z-[1] w-full h-full flex flex-col justify-between rounded-xl">
    <div class="flex items-center gap-1.5 self-end m-2 px-2.5 py-1.5 text-xl text-gray-7 font-bold font-rajdhani bg-[#0f0f1acc] rounded-full">
      <p><?= number_format($estrategia->rating_average, 1, ',', '.') ?> <span class="text-xs font-medium">/ 5</span> <span class="text-xs font-medium">• <?= $estrategia->ratings_count ?> <?= $estrategia->ratings_count == 1 ? 'avaliação' : 'avaliações' ?></span></p>
      <i class="ph-fill ph-star text-base"></i>
    </div>

    <div class="pb-5 px-5 mb-2 text-gray-6 text-sm leading-[160%] font-nunito overflow-hidden">
      <div class="mb-[-75px] group-hover:mb-[0px] group-focus:mb-[0px] transition-all ease-in-out duration-[400ms]">
        <h2 class="text-gray-7 text-xl font-bold font-rajdhani whitespace-normal break-all line-clamp-2"><?= $estrategia->title ?></h2>

        <h3 class="flex items-center gap-1.5 font-bold mt-1"><?= $estrategia->category ?> <span class="text-[10px]">•</span> <?= $estrategia->agent_name ?? 'Agente não definido' ?></h3>

        <p class="description mt-5">
          <?= $estrategia->description ?>
        </p>
      </div>
    </div>
  </article>

  <!-- Imagem / Fallback de vídeo -->
  <div class="absolute w-full h-full overflow-hidden rounded-xl">
    <?php if ($estrategia->cover_image_url): ?>
      <img src="<?= $estrategia->cover_image_url ?>" alt="Capa da estratégia" class="object-cover group-hover:scale-110 group-focus:scale-110 transition-transform duration-[400ms]">
    <?php elseif ($estrategia->video_url): ?>
      <video src="<?= $estrategia->video_url ?>" class="object-cover group-hover:scale-110 group-focus:scale-110 transition-transform duration-[400ms] video-cover" preload="metadata" muted playsinline></video>
      <div class="absolute inset-0 z-[3] flex items-center justify-center pointer-events-none">
        <span class="flex items-center justify-center bg-black/45 border border-white/40 rounded-full w-14 h-14">
          <i class="ph-fill ph-play text-white text-2xl ml-0.5"></i>
        </span>
      </div>
    <?php else: ?>
      <div class="w-full h-full bg-gray-800 flex items-center justify-center">
        <span class="text-gray-500 text-sm">Sem imagem</span>
      </div>
    <?php endif; ?>
  </div>
</a>