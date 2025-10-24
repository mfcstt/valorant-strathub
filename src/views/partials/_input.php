<?php
// Mensagens de validações de cada formulário
$validationsMessages = ($form) ? flash()->get("validations_$form") : flash()->get("validations") ?? null;

// SESSION validations_Login
$sessionLoginValidations = $_SESSION['flash_validations_login'] ?? null;

// SESSION FormData para recuperar os valores do form
$formData = flash()->get("formData") ?? [];
if ($name != 'pesquisar' && !$validationsMessages) {
  $formData = [];
}

// Determina o valor do input a partir do flash ou da requisição
$valueRaw = $formData[$name] ?? ($_REQUEST[$name] ?? '');
$value = is_string($valueRaw) ? $valueRaw : '';

// Ativar o botão de limpar o campo se campo estiver preenchido
$hidden = ($value !== '') ? '' : 'hidden';
?>

<div>
  <div class="flex items-center relative">
    <input
      type="<?= $type ?>"
      name="<?= $name ?>"
      placeholder="<?= $placeholder ?>"
      value="<?= htmlspecialchars($value) ?>"
      class="
        inpForm 
        <?php if ((!isset($validationsMessages["$name"]) && !isset($sessionLoginValidations)) || $name == 'pesquisar') echo 'valid'; ?>
        <?php if ($type == 'number') echo 'no-spinner'; ?>
      "
      <?= $name === 'titulo' ? 'maxlength="100"' : '' ?>
      required />
    <i class="
      <?php
      echo $classIcon;

      if (isset($sessionLoginValidations)) {
        echo $validationsMessages ? ' text-error-base' : ' text-gray-5';;
      } elseif (isset($validationsMessages["$name"])) {
        echo ' text-error-base';
      } else {
        echo ' text-gray-5';
      }
      ?>

      icon text-xl absolute left-4 pointer-events-none">
    </i>

    <button type="button" class=" <?= $hidden ?> cleanBtn flex absolute right-4 text-gray-4 hover:text-red-base outline-none focus:text-red-base cursor-pointer">
      <i class="ph-fill ph-x-circle text-xl"></i>
    </button>
  </div> 

  <?php if (isset($validationsMessages["$name"])): ?>
    <ul class="mt-2 ml-1 flex flex-wrap gap-x-3">
      <?php foreach ($validationsMessages["$name"] as $messages): ?>
        <li class="flex gap-1.5 items-center text-start text-error-light">
          <i class="ph ph-warning text-base"></i>
          <span class="text-xs mt-[2px]"><?= $messages ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>