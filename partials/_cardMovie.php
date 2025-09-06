<a href="/strategy?id=<?= $estrategia->id ?>" class="estrategiaCard group relative w-[280px] h-[360px] rounded-xl outline-none focus:outline-red-base transition-all ease-in-out duration-300">
  <!-- Borda -->
  <div class="absolute z-[2] w-full h-full border-2 border-gray-2 rounded-lg overflow-hidden"></div>

  <!-- Conteúdo -->
  <article class="shadingCard absolute z-[1] w-full h-full flex flex-col justify-between rounded-xl">
    <div class="flex items-center gap-1.5 self-end m-2 px-2.5 py-1.5 text-xl text-gray-7 font-bold font-rajdhani bg-[#0f0f1acc] rounded-full">
      <p><?= number_format($estrategia->rating_average, 1, ',', '.')?> <span class="text-xs font-medium">/ 5</span></p>
      <i class="ph-fill ph-star text-base"></i>
    </div>

    <div class="pb-5 px-5 mb-2 text-gray-6 text-sm leading-[160%] font-nunito overflow-hidden">
      <div class="mb-[-75px] group-hover:mb-[0px] group-focus:mb-[0px] transition-all ease-in-out duration-[400ms]">
        <h2 class="text-gray-7 text-xl font-bold font-rajdhani"><?= $estrategia->title ?></h2>

        <h3 class="flex items-center gap-1.5 font-bold mt-1"><?= $estrategia->category ?> <span class="text-[10px]">•</span> <?= $estrategia->agent_name ?? 'Agente não definido' ?></h3>

        <p class="description mt-5">
          <?= $estrategia->description ?>
        </p>
      </div>
    </div>
  </article>

  <!-- Imagem -->
  <div class="absolute w-full h-full overflow-hidden rounded-xl">
    <img src="assets/images/covers/<?= $estrategia->cover ?>" alt="Capa da estratégia" class="object-cover group-hover:scale-110 group-focus:scale-110 transition-transform duration-[400ms]">
  </div>
</a>