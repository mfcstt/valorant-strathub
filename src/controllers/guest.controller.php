<?php
// Entrar como visitante: marca sessão e redireciona para explorar
unset($_SESSION["flash_validations_login"]);
unset($_SESSION["flash_formData"]);

// Limpa qualquer auth anterior
unset($_SESSION['auth']);

// Marca modo visitante
$_SESSION['guest'] = true;

flash()->put('message', 'Você está navegando como visitante. Faça login para criar ou avaliar estratégias.');
header('Location: /explore');
exit();