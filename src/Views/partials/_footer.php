<?php declare(strict_types=1); ?>

<footer class="modalBlur border-t border-gray-2 bg-gray-1">
  <div class="max-w-[1366px] mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <a href="/explore" class="flex items-center gap-3 outline-none focus:outline-red-base rounded-md">
      <img src="/assets/icons/logo.svg" class="w-10" alt="">
      <span class="flex flex-col">
        <span class="text-gray-7 font-rajdhani text-lg font-bold leading-5">Valorant</span>
        <span class="text-gray-5 font-rajdhani text-lg font-bold leading-5">StratHub</span>
      </span>
    </a>

    <div class="text-gray-6 text-sm font-nunito leading-[160%] text-center md:text-right">
      <p>
        &copy; <?= e(date('Y')) ?> Valorant StratHub - projeto acadêmico.
        <a href="/sobre" class="text-red-light outline-none hover:text-red-base focus:text-red-base underline underline-offset-2 transition-all">
          Sobre o projeto
        </a>
      </p>
      <p class="text-gray-5 text-xs mt-1 max-w-md">
        Projeto de estudo sem vínculo com a Riot Games. Valorant e todos os ativos
        relacionados são propriedade da Riot Games, Inc.
      </p>
    </div>
  </div>
</footer>
