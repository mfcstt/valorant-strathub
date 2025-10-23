<?php
// View de Perfil do Usuário
?>

<div class="px-4 md:px-8 lg:px-16 xl:px-24 pt-16 pb-5">
  <header class="flex items-center justify-between mb-8">
    <h1 class="text-[#E5E2E9] font-rammetto text-2xl">Meu Perfil</h1>
  </header>

  <section class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8">
    <!-- Painel Esquerdo: Avatar e upload -->
    <div class="p-6 rounded-[18px] bg-gray-2">
      <div class="flex flex-col items-center gap-4">
        <?php
          $avatarUrl = (auth()->avatar && auth()->avatar !== 'avatarDefault.png') 
            ? auth()->avatar 
            : '/assets/images/avatares/avatarDefault.png';
        ?>
        <div class="relative w-32 h-32 rounded-md overflow-hidden border border-[#7435DB] bg-gray-3 shadow-buttonHover">
          <img src="<?= $avatarUrl ?>" alt="Avatar" class="w-full h-full object-cover">
        </div>

        <form action="/profile" method="post" enctype="multipart/form-data" class="w-full">
          <input type="hidden" name="action" value="update_avatar">
          <label class="block text-gray-7 font-nunito text-sm mb-2">Alterar avatar</label>
          <input type="file" name="avatar" accept="image/*" class="w-full text-gray-6 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-nunito file:bg-gray-3 file:text-gray-6 hover:file:bg-gray-4">
          <button type="submit" class="mt-4 w-full px-5 py-2 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">Atualizar avatar</button>
        </form>
      </div>
    </div>

    <!-- Painel Direito: Informações -->
    <div class="p-6 rounded-[18px] bg-gray-2">
      <form action="/profile" method="post" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <input type="hidden" name="action" value="update_info">
        <div>
          <label class="block text-gray-7 font-nunito text-sm mb-2">Nome</label>
          <input type="text" name="name" value="<?= htmlspecialchars((flash()->get('formData')['name'] ?? $user->name)) ?>" class="inpForm w-full">
        </div>
        <div>
          <label class="block text-gray-7 font-nunito text-sm mb-2">Email</label>
          <input type="text" name="email" value="<?= htmlspecialchars((flash()->get('formData')['email'] ?? $user->email)) ?>" class="inpForm w-full">
        </div>
        <div>
          <label class="block text-gray-7 font-nunito text-sm mb-2">Conta criada em</label>
          <input type="text" value="<?= htmlspecialchars(date('d/m/Y', strtotime($user->created_at))) ?>" class="inpForm w-full" disabled>
        </div>
        <div class="flex items-end">
          <button type="submit" class="px-5 py-2 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">Salvar alterações</button>
        </div>
      </form>

      <div class="mt-8">
        <h2 class="text-[#E5E2E9] font-rammetto text-xl mb-4">Atividade</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="flex items-center gap-3 px-4 py-3 rounded-md bg-gray-3">
            <i class="ph ph-film-slate text-2xl text-gray-5" title="Estratégias"></i>
            <div>
              <p class="text-[#E5E2E9] font-nunito text-base">Estratégias</p>
              <p class="text-gray-6 font-nunito text-sm"><?= (int)$strategiesCount ?> registradas</p>
            </div>
          </div>
          <div class="flex items-center gap-3 px-4 py-3 rounded-md bg-gray-3">
            <i class="ph ph-star text-2xl text-gray-5" title="Avaliações"></i>
            <div>
              <p class="text-[#E5E2E9] font-nunito text-base">Avaliações</p>
              <p class="text-gray-6 font-nunito text-sm"><?= (int)$ratingsCount ?> enviadas</p>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6 flex items-center gap-4">
        <a href="/myStrategy" class="flex items-center gap-2 px-5 py-2 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
          <i class="ph ph-stack text-xl"></i>
          Ver minhas estratégias
        </a>
      </div>
    </div>
  </section>
</div>