<?php

declare(strict_types=1);

use App\Models\Strategy;
use App\Support\Auth;

/**
 * Cabeçalho e navegação, compartilhados por todas as páginas internas -
 * inclusive a de erro, que antes ficava sem eles e obrigava quem já estava
 * logado a recomeçar a navegação do zero ao cair numa URL inválida.
 */

$user = Auth::user();
$isAdmin = Auth::isAdmin();

/** Itens de navegação: rota => [rótulo, ícone, exige login] */
$navigation = [
    '/explore' => ['Explorar', 'ph-target', false],
    '/my-strategies' => ['Minhas estratégias', 'ph-strategy', true],
    '/favorites' => ['Favoritas', 'ph-heart', true],
];

// Só quem modera vê o item - e só faz a consulta de contagem quando precisa,
// em vez de pagar essa query em toda requisição de qualquer pessoa.
$pendingCount = 0;
if ($isAdmin) {
    $navigation['/admin/moderacao'] = ['Moderação', 'ph-shield-check', true];
    $pendingCount = Strategy::pendingCount();
}

$currentPath = '/' . ltrim((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? ''), '/');

$avatarUrl = ($user !== null && $user->avatar && $user->avatar !== 'avatarDefault.png')
    ? (string) $user->avatar
    : '/assets/images/avatares/avatarDefault.png';
?>

<header class="modalBlur border-b border-gray-2 bg-gray-1 z-10">
  <div class="max-w-[1366px] h-20 flex justify-between items-center px-6 mx-auto font-nunito">
    <a href="/explore" class="flex items-center gap-3 outline-none focus:outline-red-base rounded-md">
      <img src="/assets/icons/logo.svg" class="w-12" alt="">
      <span class="flex flex-col">
        <span class="text-gray-7 font-rajdhani text-lg font-bold leading-5">Valorant</span>
        <span class="text-gray-5 font-rajdhani text-lg font-bold leading-5">StratHub</span>
      </span>
    </a>

    <nav class="hidden md:block" aria-label="Navegação principal">
      <ul class="flex items-center gap-6 text-gray-5">
        <?php foreach ($navigation as $path => [$label, $icon, $requiresAuth]): ?>
          <?php if ($requiresAuth && $user === null) {
              continue;
          } ?>
          <li>
            <a href="<?= e($path) ?>"
              class="flex items-center gap-2 px-3 py-2 rounded-md outline-none hover:bg-gray-2 focus:outline-red-base transition-all duration-300 <?= $currentPath === $path ? 'componentActive' : '' ?>"
              <?= $currentPath === $path ? 'aria-current="page"' : '' ?>>
              <i class="ph <?= e($icon) ?> text-xl" aria-hidden="true"></i>
              <?= e($label) ?>
              <?php if ($path === '/admin/moderacao' && $pendingCount > 0): ?>
                <span class="flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-red-base text-white text-xs font-bold">
                  <?= e($pendingCount) ?>
                </span>
              <?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="flex items-center">
      <div class="hidden md:flex items-center">
        <div class="flex items-center gap-2 pr-3 border-r border-gray-3">
          <span class="text-gray-6 text-sm leading-[160%] capitalize flex items-center gap-3">
            Olá, <?= e($user !== null ? $user->name : 'Visitante') ?>
            <?php if ($user !== null && $user->elo): ?>
              <img src="/assets/images/elos/<?= e(strtolower((string) $user->elo)) ?>.png"
                alt="Elo <?= e(ucfirst(strtolower((string) $user->elo))) ?>" class="w-7 h-7">
            <?php endif; ?>
          </span>

          <?php if ($user !== null): ?>
            <a href="/profile" title="Abrir perfil"
              class="relative rounded-md w-9 h-9 overflow-hidden border border-gray-4 bg-gray-3 outline-none focus:outline-red-base">
              <img src="<?= e($avatarUrl) ?>" alt="" class="w-full h-full object-cover">
            </a>
          <?php else: ?>
            <a href="/login" title="Fazer login"
              class="relative rounded-md w-9 h-9 overflow-hidden border border-gray-3 bg-gray-3 outline-none focus:outline-red-base">
              <img src="/assets/images/avatares/avatarDefault.png" alt="" class="w-full h-full object-cover">
            </a>
          <?php endif; ?>
        </div>

        <?php if ($user !== null): ?>
          <!-- Logout por POST: um GET poderia ser disparado por qualquer site
               de terceiros e encerrar a sessão sem a pessoa pedir. -->
          <form method="post" action="/logout" class="ml-3">
            <?= csrf_field() ?>
            <button type="submit"
              class="h-8 flex items-center text-gray-5 p-1.5 bg-gray-3 rounded-md outline-none hover:text-red-light focus:text-red-light focus:outline-red-base transition-all duration-300"
              aria-label="Sair da conta">
              <i class="ph ph-sign-out text-[20px]" aria-hidden="true"></i>
            </button>
          </form>
        <?php else: ?>
          <a href="/login"
            class="h-8 flex items-center ml-3 text-gray-5 p-1.5 bg-gray-3 rounded-md outline-none hover:text-red-light focus:text-red-light focus:outline-red-base transition-all duration-300"
            aria-label="Entrar">
            <i class="ph ph-sign-in text-[20px]" aria-hidden="true"></i>
          </a>
        <?php endif; ?>
      </div>

      <button id="mobileMenuToggle" type="button" aria-expanded="false" aria-controls="mobileMenuPanel"
        class="md:hidden h-10 w-10 flex items-center justify-center rounded-md bg-gray-3 text-gray-5 outline-none hover:text-red-light focus:text-red-light focus:outline-red-base transition-all duration-300"
        aria-label="Abrir menu">
        <i class="ph ph-list text-2xl" aria-hidden="true"></i>
      </button>
    </div>
  </div>

  <!-- Menu mobile -->
  <div id="mobileMenuBackdrop" class="hidden fixed inset-0 z-40"></div>
  <div id="mobileMenuPanel"
    class="hidden fixed top-20 right-4 w-[280px] rounded-md bg-gray-1 border border-gray-3 z-50 shadow-buttonHover">
    <nav class="flex flex-col" aria-label="Navegação mobile">
      <?php foreach ($navigation as $path => [$label, $icon, $requiresAuth]): ?>
        <?php if ($requiresAuth && $user === null) {
            continue;
        } ?>
        <a href="<?= e($path) ?>" class="flex items-center gap-2 px-4 py-3 text-gray-6 hover:bg-gray-2">
          <i class="ph <?= e($icon) ?> text-xl" aria-hidden="true"></i>
          <?= e($label) ?>
          <?php if ($path === '/admin/moderacao' && $pendingCount > 0): ?>
            <span class="flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-red-base text-white text-xs font-bold">
              <?= e($pendingCount) ?>
            </span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>

      <div class="border-t border-gray-3 my-2"></div>

      <?php if ($user !== null): ?>
        <a href="/profile" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-2">
          <img src="<?= e($avatarUrl) ?>" alt="" class="w-9 h-9 rounded-md border border-gray-4 object-cover">
          <span class="flex-1">
            <span class="block text-gray-7 font-nunito text-sm leading-[160%]"><?= e($user->name) ?></span>
            <?php if ($user->elo): ?>
              <span class="block text-gray-5 text-xs capitalize"><?= e($user->elo) ?></span>
            <?php endif; ?>
          </span>
        </a>

        <form method="post" action="/logout">
          <?= csrf_field() ?>
          <button type="submit" class="w-full flex items-center gap-2 px-4 py-3 text-gray-6 hover:bg-gray-2">
            <i class="ph ph-sign-out text-xl" aria-hidden="true"></i>
            Sair
          </button>
        </form>
      <?php else: ?>
        <a href="/login" class="flex items-center gap-2 px-4 py-3 text-gray-6 hover:bg-gray-2">
          <i class="ph ph-user text-xl" aria-hidden="true"></i>
          Entrar
        </a>
      <?php endif; ?>
    </nav>
  </div>
</header>
