<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\UploadValidator;

/**
 * Página de perfil: avatar, dados, atividade, segurança e exclusão de conta.
 *
 * @var \App\Models\User $user
 * @var int              $strategies_count
 * @var int              $ratings_count
 * @var int              $favorites_count
 */

$avatarUrl = ($user->avatar && $user->avatar !== 'avatarDefault.png')
    ? (string) $user->avatar
    : '/assets/images/avatares/avatarDefault.png';

$selectedElo = strtolower(old('elo', (string) ($user->elo ?? 'ferro')));
$createdAt = $user->created_at !== null ? strtotime((string) $user->created_at) : false;
$maxImageMb = intdiv(UploadValidator::MAX_IMAGE_BYTES, 1024 * 1024);

/** @var list<array{icon: string, label: string, value: int, suffix: string}> */
$activity = [
    ['icon' => 'ph-strategy', 'label' => 'Estratégias', 'value' => $strategies_count, 'suffix' => 'publicadas'],
    ['icon' => 'ph-star', 'label' => 'Avaliações', 'value' => $ratings_count, 'suffix' => 'enviadas'],
    ['icon' => 'ph-heart', 'label' => 'Favoritas', 'value' => $favorites_count, 'suffix' => 'salvas'],
];
?>

<div class="px-4 md:px-8 lg:px-16 xl:px-24 pt-4 pb-8">
  <header class="mb-8">
    <h1 class="text-gray-7 font-rammetto text-2xl">Meu perfil</h1>
  </header>

  <section class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8">
    <!-- Avatar -->
    <div class="p-6 rounded-[18px] bg-gray-2">
      <div class="flex flex-col items-center gap-4">
        <div class="relative w-32 h-32 rounded-md overflow-hidden border border-[#7435DB] bg-gray-3 shadow-buttonHover">
          <img src="<?= e($avatarUrl) ?>" alt="Seu avatar" class="w-full h-full object-cover">
        </div>

        <?php if ($user->elo): ?>
          <div class="flex items-center gap-2 text-gray-6 font-nunito text-sm">
            <img src="/assets/images/elos/<?= e($selectedElo) ?>.png" alt="" class="w-6 h-6">
            <span class="capitalize"><?= e($user->elo) ?></span>
          </div>
        <?php endif; ?>

        <form action="/profile" method="post" enctype="multipart/form-data" class="w-full">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update_avatar">

          <label for="avatar" class="block text-gray-7 font-nunito text-sm mb-2">Alterar avatar</label>
          <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp,image/avif"
            class="w-full text-gray-6 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-nunito file:bg-gray-3 file:text-gray-6 hover:file:bg-gray-4">
          <p class="mt-2 text-xs text-gray-5 font-nunito">JPG, PNG ou WebP até <?= e($maxImageMb) ?> MB.</p>

          <button type="submit"
            class="mt-4 w-full px-5 py-2 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
            Enviar
          </button>
        </form>
      </div>
    </div>

    <div class="p-6 rounded-[18px] bg-gray-2">
      <!-- Dados da conta -->
      <h2 class="text-gray-7 font-rammetto text-xl mb-4">Dados da conta</h2>

      <form action="/profile" method="post" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_info">

        <div>
          <label for="name" class="block text-gray-7 font-nunito text-sm mb-2">Nome</label>
          <div class="relative">
            <i class="ph ph-user text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
              aria-hidden="true"></i>
            <input type="text" id="name" name="name" required maxlength="60"
              value="<?= e(old('name', (string) $user->name)) ?>" class="inpForm pl-10 w-full">
          </div>
        </div>

        <div>
          <label for="email" class="block text-gray-7 font-nunito text-sm mb-2">E-mail</label>
          <div class="relative">
            <i class="ph ph-envelope text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
              aria-hidden="true"></i>
            <input type="email" id="email" name="email" required maxlength="255"
              value="<?= e(old('email', (string) $user->email)) ?>" class="inpForm pl-10 w-full">
          </div>
        </div>

        <div>
          <label for="profile-elo" class="block text-gray-7 font-nunito text-sm mb-2">Elo</label>
          <div class="relative flex items-center gap-3">
            <i class="ph ph-shield-star text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
              aria-hidden="true"></i>
            <select id="profile-elo" name="elo" class="inpForm pl-10 flex-1" data-elo-select>
              <?php foreach (User::ELOS as $elo): ?>
                <option value="<?= e($elo) ?>" <?= $selectedElo === $elo ? 'selected' : '' ?>>
                  <?= e(ucfirst($elo)) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <img data-elo-preview src="/assets/images/elos/<?= e($selectedElo) ?>.png" alt=""
              class="w-12 h-12 rounded-md border border-gray-4 bg-gray-1/80">
          </div>
        </div>

        <div>
          <label for="created-at" class="block text-gray-7 font-nunito text-sm mb-2">Conta criada em</label>
          <div class="relative">
            <i class="ph ph-calendar text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
              aria-hidden="true"></i>
            <input type="text" id="created-at" class="inpForm pl-10 w-full" disabled
              value="<?= e($createdAt !== false ? date('d/m/Y', $createdAt) : '—') ?>">
          </div>
        </div>

        <div class="flex items-end md:col-span-2">
          <button type="submit"
            class="px-5 py-2 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
            Salvar alterações
          </button>
        </div>
      </form>

      <!-- Atividade -->
      <div class="mt-10">
        <h2 class="text-gray-7 font-rammetto text-xl mb-4">Atividade</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <?php foreach ($activity as $item): ?>
            <div class="flex items-center gap-3 px-4 py-3 rounded-md bg-gray-3">
              <i class="ph <?= e($item['icon']) ?> text-2xl text-gray-5" aria-hidden="true"></i>
              <div>
                <p class="text-gray-7 font-nunito text-base"><?= e($item['label']) ?></p>
                <p class="text-gray-6 font-nunito text-sm">
                  <?= e($item['value']) ?> <?= e($item['suffix']) ?>
                </p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="mt-6">
          <a href="/my-strategies"
            class="inline-flex items-center gap-2 px-5 py-2 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
            <i class="ph ph-stack text-xl" aria-hidden="true"></i>
            Ver minhas estratégias
          </a>
        </div>
      </div>

      <!-- Segurança -->
      <div class="mt-10">
        <h2 class="text-gray-7 font-rammetto text-xl mb-4">Segurança</h2>

        <form action="/profile" method="post" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="change_password">

          <div class="md:col-span-2">
            <label for="senha_atual" class="block text-gray-7 font-nunito text-sm mb-2">Senha atual</label>
            <div class="relative">
              <i class="ph ph-lock text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
                aria-hidden="true"></i>
              <input type="password" id="senha_atual" name="senha_atual" required autocomplete="current-password"
                class="inpForm pl-10 w-full" placeholder="Sua senha atual">
            </div>
          </div>

          <div>
            <label for="nova_senha" class="block text-gray-7 font-nunito text-sm mb-2">Nova senha</label>
            <div class="relative">
              <i class="ph ph-lock-key text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
                aria-hidden="true"></i>
              <input type="password" id="nova_senha" name="nova_senha" required autocomplete="new-password"
                class="inpForm pl-10 w-full" placeholder="Mínimo de 8 caracteres">
            </div>
          </div>

          <div>
            <label for="confirmar_senha" class="block text-gray-7 font-nunito text-sm mb-2">Confirmar nova senha</label>
            <div class="relative">
              <i class="ph ph-lock-key text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
                aria-hidden="true"></i>
              <input type="password" id="confirmar_senha" name="confirmar_senha" required autocomplete="new-password"
                class="inpForm pl-10 w-full" placeholder="Repita a nova senha">
            </div>
          </div>

          <p class="md:col-span-2 text-xs text-gray-5 font-nunito">
            Ao trocar a senha, as sessões abertas em outros dispositivos são encerradas.
          </p>

          <div class="flex items-end md:col-span-2">
            <button type="submit"
              class="px-5 py-2 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
              Alterar senha
            </button>
          </div>
        </form>
      </div>

      <!-- Exclusão de conta -->
      <div class="mt-10 pt-8 border-t border-gray-3">
        <h2 class="text-error-light font-rammetto text-xl mb-2">Apagar conta</h2>
        <p class="text-gray-6 font-nunito text-sm mb-4">
          Ação permanente. Suas estratégias, avaliações e favoritas serão removidas junto com a conta.
        </p>

        <form action="/profile" method="post" class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-6"
          data-confirm="Apagar sua conta e todo o conteúdo publicado? Esta ação não pode ser desfeita.">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete_account">

          <div>
            <label for="delete_senha" class="block text-gray-7 font-nunito text-sm mb-2">Confirme com sua senha</label>
            <div class="relative">
              <i class="ph ph-lock text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
                aria-hidden="true"></i>
              <input type="password" id="delete_senha" name="senha_atual" required autocomplete="current-password"
                class="inpForm pl-10 w-full" placeholder="Digite sua senha">
            </div>
          </div>

          <div class="flex items-end">
            <button type="submit"
              class="px-5 py-2 rounded-md text-white font-nunito bg-error-base outline-none hover:bg-error-light focus:bg-error-light focus:outline-red-base transition-all ease-in-out duration-300">
              Apagar conta
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>

<?php flash()->forget('formData'); ?>
