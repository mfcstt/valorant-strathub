<?php

declare(strict_types=1);

use App\Models\User;

/**
 * Tela de acesso: login e cadastro na mesma página, alternados por CSS.
 *
 * Os erros são lidos com `peek()` (não consome), porque a mesma chave é
 * consultada várias vezes ao desenhar os dois formulários. O consumo acontece
 * uma única vez, no fim do arquivo.
 */
$loginErrors = flash()->peek('validations_login') ?? [];
$registerErrors = flash()->peek('validations_register') ?? [];

$showRegister = $registerErrors !== [];
$activeForm = $showRegister ? 'register' : 'login';
$selectedElo = strtolower(trim(old('elo')));
?>

<!-- Login e cadastro -->
<section class="min-h-screen flex p-4 md:items-stretch md:justify-start items-center justify-center">
  <!-- Arte lateral -->
  <!-- bg-left-top, e não bg-center: a imagem tem a logo "VALORANT" colada no
       canto superior esquerdo — centralizar o recorte cortava o próprio texto. -->
  <div class="hidden md:flex md:flex-col justify-between md:w-2/4 md:min-h-screen p-8 rounded-[18px] bg-thumb bg-cover bg-no-repeat bg-left-top"></div>

  <!-- Formulários -->
  <!-- Sem padding-top fixo: o flex items-center abaixo já centraliza o
       formulário verticalmente. Um `pt-[135px]` fixo empurrava o conteúdo
       para além do centro, deixando um vão vazio desproporcional no topo. -->
  <div class="w-full md:w-2/4 text-gray-5 flex items-center justify-center">
    <div class="flex flex-col font-nunito">
      <header class="flex gap-1 w-[328px] mx-auto p-1 rounded-[10px] bg-gray-2 text-center">
        <div class="relative flex-1 rounded-md">
          <input type="checkbox" id="btnL" class="checkbox absolute opacity-0 pointer-events-none"
            <?= $showRegister ? '' : 'checked' ?>>
          <label for="btnL" class="block w-full h-full px-3 py-2 rounded-md focus:outline-red-base cursor-pointer">Login</label>
        </div>

        <div class="relative flex-1 rounded-md">
          <input type="checkbox" id="btnR" class="checkbox absolute opacity-0 pointer-events-none"
            <?= $showRegister ? 'checked' : '' ?>>
          <label for="btnR" class="block w-full h-full px-3 py-2 rounded-md focus:outline-red-base cursor-pointer">Cadastro</label>
        </div>
      </header>

      <div class="flex justify-center md:gap-80 gap-0 overflow-hidden">
        <!-- Login -->
        <section id="login" class="text-center w-[328px] <?= $showRegister ? 'hidden disabled' : '' ?>">
          <h1 class="w-[328px] mt-[52px] mb-5 text-2xl text-gray-7 text-start font-rammetto">Acesse sua conta</h1>

          <form action="/login" method="post" novalidate>
            <?= csrf_field() ?>

            <div class="flex flex-col gap-4">
              <?php input('email', 'email', 'E-mail', 'ph ph-envelope', 'login'); ?>
              <?php input('password', 'senha', 'Senha', 'ph ph-password', 'login'); ?>
            </div>

            <button type="submit"
              class="submit w-full mt-8 px-5 py-3 rounded-md bg-red-base text-white hover:bg-red-light hover:shadow-buttonHover focus:bg-red-light focus:shadow-buttonHover outline-none">
              Entrar
            </button>
          </form>
        </section>

        <!-- Cadastro -->
        <section id="register" class="text-center w-[328px] <?= $showRegister ? '' : 'hidden' ?>">
          <h1 class="mt-[52px] mb-5 text-2xl text-gray-7 text-start font-rammetto">Crie sua conta</h1>

          <form action="/register" method="post" novalidate>
            <?= csrf_field() ?>

            <div class="flex flex-col gap-4">
              <?php input('text', 'nome', 'Nome', 'ph ph-user', 'register'); ?>
              <?php input('email', 'email', 'E-mail', 'ph ph-envelope', 'register'); ?>
              <?php input('password', 'senha', 'Senha', 'ph ph-password', 'register'); ?>

              <p class="-mt-2 text-xs text-gray-5 text-start leading-relaxed">
                A senha deve ter no mínimo 8 caracteres e incluir ao menos um caractere especial.
              </p>

              <div class="relative flex items-center">
                <i class="ph ph-shield-star text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5"
                  aria-hidden="true"></i>
                <label for="elo" class="sr-only">Elo</label>
                <select id="elo" name="elo" class="inpForm pl-10 w-full">
                  <option value="" <?= $selectedElo === '' ? 'selected' : '' ?>>Selecione seu elo</option>
                  <?php foreach (User::ELOS as $elo): ?>
                    <option value="<?= e($elo) ?>" <?= $selectedElo === $elo ? 'selected' : '' ?>>
                      <?= e(ucfirst($elo)) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <?php if (!empty($registerErrors['elo'])): ?>
                <ul class="text-start">
                  <?php foreach ($registerErrors['elo'] as $message): ?>
                    <li class="flex gap-1.5 items-center text-error-light text-xs">
                      <i class="ph ph-warning text-base" aria-hidden="true"></i>
                      <span><?= e($message) ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>

            <button type="submit"
              class="submit w-full mt-8 px-5 py-3 rounded-md bg-red-base text-white hover:bg-red-light hover:shadow-buttonHover focus:bg-red-light focus:shadow-buttonHover outline-none">
              Criar
            </button>
          </form>
        </section>
      </div>

      <!-- Acesso sem conta -->
      <div class="mt-8 w-[328px] mx-auto text-center">
        <a href="/guest"
          class="flex w-full items-center justify-center gap-2 px-5 py-3 rounded-md bg-gray-3 text-gray-7 outline-none hover:bg-gray-2 focus:outline-red-base transition-all duration-300">
          <i class="ph ph-eye text-xl" aria-hidden="true"></i>
          Entrar como visitante
        </a>
      </div>
    </div>
  </div>
</section>

<?php
// Agora que os dois formulários já foram desenhados, os dados de uma tentativa
// anterior podem sair da sessão.
flash()->forget('validations_' . $activeForm);
flash()->forget('formData');
