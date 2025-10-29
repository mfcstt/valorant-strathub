<?php
// Entrar como visitante: marca sessão e redireciona para explorar
unset($_SESSION["flash_validations_login"]);
unset($_SESSION["flash_formData"]);

// Limpa qualquer auth anterior
unset($_SESSION['auth']);

// Marca modo visitante
$_SESSION['guest'] = true;

// Garantir que cookies de auth não reidratem sessão enquanto visitante
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
setcookie('auth_uid', '', [
  'expires' => time() - 3600,
  'path' => '/',
  'secure' => $isSecure,
  'httponly' => true,
  'samesite' => 'Lax',
]);
setcookie('auth_sig', '', [
  'expires' => time() - 3600,
  'path' => '/',
  'secure' => $isSecure,
  'httponly' => true,
  'samesite' => 'Lax',
]);

flash()->put('message', 'Você está navegando como visitante. Faça login para criar ou avaliar estratégias.');
header('Location: /explore');
exit();