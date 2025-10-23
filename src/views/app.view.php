<header class="modalBlur border-b border-gray-2 bg-gray-1 overflow-hidden z-10">
  <div class="max-w-[1366px] h-20 flex justify-between items-center px-6 mx-auto font-nunito">
    <a href="/explore" class="flex items-center gap-3">
      <img src="assets/icons/logo.svg" class="w-12" alt="Logo Valorant Strathub">
      <div class="flex flex-col">
        <span class="text-gray-7 font-rajdhani text-lg font-bold leading-5">Valorant</span>
        <span class="text-gray-5 font-rajdhani text-lg font-bold leading-5">Strathub</span>
      </div>
    </a>

    <nav>
      <ul class="flex items-center gap-6 text-gray-5">
        <li>
          <a href="/explore" class="flex items-center gap-2 px-3 py-2 rounded-md outline-none hover:bg-gray-2 focus:outline-red-base transition-all duration-300 <?php if ($component == 'explore')
            echo 'componentActive'; ?>">
            <i class="ph ph-target text-xl"></i>
            Explorar
          </a>
        </li>
        <li>
          <a href="myStrategy" class="flex items-center gap-2 px-3 py-2 rounded-md outline-none hover:bg-gray-2 focus:outline-red-base transition-all duração-300 <?php if ($component == 'myStrategy')
            echo 'componentActive'; ?>">
            <i class="ph ph-film-slate text-xl"></i>
            Minhas estratégias
          </a>
        </li>
      </ul>
    </nav>

    <div class="flex">
      <div class="flex items-center gap-2 pr-3 border-r border-gray-3">
        <span class="text-gray-6 text-sm leading-[160%] capitalize flex items-center gap-3">Olá,
          <?= auth() ? auth()->name : 'Visitante' ?>
          <?php if (auth() && (auth()->elo ?? null)): ?>
            <img src="/assets/images/elos/<?= strtolower(auth()->elo) ?>.png" alt="Elo" class="w-8 h-8"
              title="Elo: <?= ucfirst(strtolower(auth()->elo)) ?>">
          <?php endif; ?>
        </span>

        <?php if (auth()): ?>
          <a href="/profile" title="Abrir perfil"
            class="relative rounded-md w-9 h-9 overflow-hidden border border-[#7435DB] bg-gray-3">
            <img
              src="<?= (auth()->avatar && auth()->avatar !== 'avatarDefault.png') ? auth()->avatar : '/assets/images/avatares/avatarDefault.png' ?>"
              alt="Avatar" class="w-full h-full object-cover">
          </a>
        <?php else: ?>
          <a href="/login" title="Fazer login"
            class="relative rounded-md w-9 h-9 overflow-hidden border border-gray-3 bg-gray-3">
            <img src="/assets/images/avatares/avatarDefault.png" alt="Avatar" class="w-full h-full object-cover">
          </a>
        <?php endif; ?>
      </div>

      <a href="/logout"
        class="h-8 text-gray-5 p-1.5 bg-gray-3 rounded-md ml-3 outline-none hover:text-red-light focus:text-red-light focus:outline-red-base transition-all duration-300">
        <i class="ph ph-sign-out text-[20px] my-auto"></i>
      </a>
    </div>
  </div>
</header>

<main>
  <div class="max-w-[1366px] mx-auto pt-14 md:pt-16">
    <?php require "../src/views/components/{$component}.component.php"; ?>
  </div>
</main>