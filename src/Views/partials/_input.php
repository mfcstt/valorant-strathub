<?php

declare(strict_types=1);

/**
 * Campo de formulário com ícone, botão de limpar e lista de erros.
 *
 * Recebe as variáveis $type, $name, $placeholder, $iconClass e $form da função
 * `input()`. Os erros são lidos com `peek()` porque a mesma chave é consultada
 * por todos os campos do formulário na mesma renderização.
 */

/** @var string $type */
/** @var string $name */
/** @var string $placeholder */
/** @var string $iconClass */
/** @var string|null $form */

$errorsKey = $form !== null && $form !== '' ? "validations_{$form}" : 'validations';
$allErrors = flash()->peek($errorsKey) ?? [];
$fieldErrors = is_array($allErrors) ? ($allErrors[$name] ?? []) : [];

// Campos de senha nunca são repopulados: devolver a senha digitada no HTML a
// deixaria visível no código-fonte da página e no histórico do navegador.
$value = $type === 'password'
    ? ''
    : old($name, $name === 'pesquisar' ? (string) ($_GET[$name] ?? '') : '');

$hasError = $fieldErrors !== [];
$inputId = 'field-' . $name;
$errorId = $inputId . '-error';

// O autocomplete de senha precisa distinguir login de cadastro: "senha" é o
// mesmo $name nos dois formulários, mas o gerenciador de senha do navegador
// só sugere uma senha nova quando o hint é "new-password" — com
// "current-password" (valor anterior, fixo para todo campo de senha) ele
// tentava preencher com uma senha já salva também na tela de cadastro.
$autocomplete = match (true) {
    $type === 'password' && $form === 'register' => 'new-password',
    $type === 'password' => 'current-password',
    $type === 'email' => 'email',
    $name === 'nome' => 'name',
    default => null,
};
?>

<div>
  <div class="flex items-center relative">
    <label for="<?= e($inputId) ?>" class="sr-only"><?= e($placeholder) ?></label>

    <input
      id="<?= e($inputId) ?>"
      type="<?= e($type) ?>"
      name="<?= e($name) ?>"
      placeholder="<?= e($placeholder) ?>"
      value="<?= e($value) ?>"
      class="inpForm <?= $hasError && $name !== 'pesquisar' ? '' : 'valid' ?> <?= $type === 'number' ? 'no-spinner' : '' ?>"
      <?= $name === 'titulo' ? 'maxlength="100"' : '' ?>
      <?= $autocomplete !== null ? 'autocomplete="' . e($autocomplete) . '"' : '' ?>
      <?= $hasError ? 'aria-invalid="true" aria-describedby="' . e($errorId) . '"' : '' ?>
      <?= $name === 'pesquisar' ? '' : 'required' ?> />

    <i class="<?= e($iconClass) ?> <?= $hasError ? 'text-error-base' : 'text-gray-5' ?> icon text-xl absolute left-4 pointer-events-none"
      aria-hidden="true"></i>

    <button type="button"
      class="<?= $value === '' ? 'hidden' : '' ?> cleanBtn flex absolute right-4 text-gray-4 hover:text-red-base outline-none focus:text-red-base cursor-pointer"
      aria-label="Limpar campo">
      <i class="ph-fill ph-x-circle text-xl" aria-hidden="true"></i>
    </button>
  </div>

  <?php if ($hasError): ?>
    <ul id="<?= e($errorId) ?>" class="mt-2 ml-1 flex flex-wrap gap-x-3">
      <?php foreach ($fieldErrors as $message): ?>
        <li class="flex gap-1.5 items-center text-start text-error-light">
          <i class="ph ph-warning text-base" aria-hidden="true"></i>
          <span class="text-xs mt-[2px]"><?= e($message) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
