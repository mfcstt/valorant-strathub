<?php
$form = '';
$formLogin = null;
$formRegister = null;

if (flash()->get('validations_login') ?? []) {
  $formLogin = 'login';
  $form = "_$formLogin";
} else if (flash()->get('validations_register') ?? []) {
  $formRegister = 'register';
  $form = "_$formRegister";
}
?>

<!-- Login e Cadastro-->
<section class="min-h-screen flex p-4 md:items-stretch md:justify-start items-center justify-center">
  <!-- Thumb -->
  <div class="hidden md:flex md:flex-col justify-between md:w-2/4 md:h-full p-8 rounded-[18px] bg-thumb bg-cover bg-no-repeat bg-center">
  </div>

  <!-- Forms -->
  <div class="w-full md:w-2/4 text-gray-5 md:pt-[135px] pt-0 flex items-center justify-center">
    <div class="flex flex-col font-nunito">
      <header class="flex gap-1 w-[328px] mx-auto p-1 rounded-[10px] bg-gray-2 text-center">
        <div class="relative flex-1 rounded-md">
          <input
            type="checkbox"
            id="btnL"
            class="checkbox absolute opacity-0 pointer-events-none"
            <?php if (!isset($formRegister)) echo 'checked'; ?> >

          <label for="btnL" class="block w-full h-full px-3 py-2 rounded-md focus:outline-red-base cursor-pointer">Login </label>
        </div>

        <div class="relative flex-1 rounded-md">
          <input
            type="checkbox"
            id="btnR"
            class="checkbox absolute opacity-0 pointer-events-none"
            <?php if (isset($formRegister)) echo 'checked'; ?> >

          <label for="btnR" class="block w-full h-full px-3 py-2 rounded-md focus:outline-red-base cursor-pointer">Cadastro </label>
        </div>
      </header>

      <div class="flex justify-center md:gap-80 gap-0 overflow-hidden">
        <!-- Login -->
        <section id="login" class="text-center w-[328px] <?php if (isset($formRegister)) echo 'hidden disabled'; ?> ">
          <h1 class="w-[328px] mt-[52px] mb-5 text-2xl text-gray-7 text-start font-rammetto">Acesse sua conta</h1>

          <form method="post" novalidate>
            <div class="flex flex-col gap-4">
              <?php input('email', 'email', 'E-mail', 'ph ph-envelope', $formLogin); ?>

              <?php input('password', 'senha', 'Senha', 'ph ph-password', $formLogin); ?>
            </div>

            <button type="submit" class="submit w-full mt-8 px-5 py-3 rounded-md bg-red-base text-white hover:bg-red-light hover:shadow-buttonHover focus:bg-red-light focus:shadow-buttonHover outline-none">Entrar</button>
          </form>
        </section>

        <!-- Cadastro -->
        <section id="register" class="text-center w-[328px] <?php if (!isset($formRegister)) echo 'hidden'; ?>  ">
          <h1 class="mt-[52px] mb-5 text-2xl text-gray-7 text-start font-rammetto">Crie sua conta</h1>

          <form action="/register" method="post" novalidate>
            <div class="flex flex-col gap-4">
              <?php input('text', 'nome', 'Nome', 'ph ph-user', $formRegister); ?>

              <?php input('email', 'email', 'E-mail', 'ph ph-envelope', $formRegister); ?>

              <?php input('password', 'senha', 'Senha', 'ph ph-password', $formRegister); ?>

              <?php 
                $selectedElo = '';
                if (isset($formRegister)) {
                  $fd = flash()->get('formData') ?? [];
                  $selectedElo = strtolower(trim($fd['elo'] ?? ''));
                }
                $elos = ['ferro','bronze','prata','ouro','platina','diamante','ascendente','imortal','radiante'];
                $iconClass = 'ph ph-shield-star';
                $validationsRegister = flash()->get('validations_register') ?? [];
              ?>
              <div class="relative flex items-center">
                <i class="<?= $iconClass ?> text-xl absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-5" title="Elo"></i>
                <select name="elo" class="inpForm pl-10 w-full">
                  <option value="" <?= $selectedElo === '' ? 'selected' : '' ?>>Selecione seu elo</option>
                  <?php foreach ($elos as $e): ?>
                    <option value="<?= $e ?>" <?= $selectedElo === $e ? 'selected' : '' ?>><?= ucfirst($e) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if (!empty($validationsRegister['elo'] ?? [])): ?>
                <p class="text-red-base text-sm text-start"><?= implode('<br>', $validationsRegister['elo']) ?></p>
              <?php endif; ?>
            </div>

            <button type="submit" class="submit w-full mt-8 px-5 py-3 rounded-md bg-red-base text-white hover:bg-red-light hover:shadow-buttonHover focus:bg-red-light focus:shadow-buttonHover outline-none">Criar</button>
          </form>
        </section>
      </div>

      <!-- Entrar como visitante -->
      <div class="mt-8 w-[328px] mx-auto text-center">
        <a href="/guest" class="flex w-full items-center justify-center gap-2 px-5 py-3 rounded-md bg-gray-3 text-gray-7 outline-none hover:bg-gray-2 focus:outline-red-base transition-all duration-300">
          <i class="ph ph-eye text-xl"></i>
          Entrar como visitante
        </a>
      </div>
    </div>
  </div>
</section>

<?php
// Limpar os dados das sessões após utiliza-los
unset($_SESSION["flash_validations$form"]);
unset($_SESSION["flash_formData"]);
?>