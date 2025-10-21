<header class="modalBlur border-b border-gray-2 bg-gray-1 overflow-hidden">
  <div class="max-w-[1366px] h-20 flex justify-between items-center px-6 mx-auto font-nunito">
    <img src="assets/icons/logo.svg" class="w-12" alt="Logo AB Filmes">

    <nav>
      <ul class="flex items-center gap-6 text-gray-5">
        <li>
          <a href="/explore" class="flex items-center gap-2 px-3 py-2 rounded-md outline-none hover:bg-gray-2 focus:outline-red-base transition-all duration-300 <?php if ($component == 'explore') echo 'componentActive'; ?>">
            <i class="ph ph-target text-xl"></i> 
            Explorar
          </a>
        </li>
        <li>
          <a href="myStrategy" class="flex items-center gap-2 px-3 py-2 rounded-md outline-none hover:bg-gray-2 focus:outline-red-base transition-all duration-300 <?php if ($component == 'myStrategy') echo 'componentActive'; ?>">
            <i class="ph ph-film-slate text-xl"></i>
            Minhas estratégias
          </a>
        </li>
      </ul>
    </nav>

    <div class="flex">
      <div class="flex items-center gap-3 pr-3 border-r border-gray-3">
        <span class="text-gray-6 text-sm leading-[160%] capitalize">Olá, <?= auth() ? auth()->name : 'Visitante' ?> </span>

        <?php if (auth()): ?>
          <form id="formAvatarProfile" action="/explore" method="post" enctype="multipart/form-data" class="relative rounded-md w-9 h-9 overflow-hidden border border-[#7435DB] bg-gray-3">
            <label title="Alterar imagem de perfil" class="group cursor-pointer">
              <div class="absolute left-0 top-0 w-full h-full flex items-center justify-center pt-1 rounded-md bg-[#a85fdd80] opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-all duration-300">
                <i class="ph ph-upload-simple text-2xl text-gray-7"></i>
              </div>

              <img 
                src="/assets/images/avatares/<?= auth()->avatar ?? 'avatarDefault.png' ?>"
                alt="Avatar perfil" 
                class="w-full h-full object-cover"
              >
              
              <input type="file" name="avatar" class="absolute inset-0 z-[-1] opacity-0" id="avatarProfile">
            </label>
          </form>
        <?php else: ?>
          <div class="relative rounded-md w-9 h-9 overflow-hidden border border-gray-3 bg-gray-3">
            <img 
              src="/assets/images/avatares/avatarDefault.png"
              alt="Avatar padrão" 
              class="w-full h-full object-cover"
            >
          </div>
        <?php endif; ?>
      </div>

      <a href="/logout" class="h-8 text-gray-5 p-1.5 bg-gray-3 rounded-md ml-3 outline-none hover:text-red-light focus:text-red-light focus:outline-red-base transition-all duration-300">
        <i class="ph ph-sign-out text-[20px] my-auto"></i>
      </a>
    </div>
  </div>
</header>

<main>
  <div class="max-w-[1366px] mx-auto">
    <?php require "../src/views/components/{$component}.component.php"; ?>
  </div>
</main>