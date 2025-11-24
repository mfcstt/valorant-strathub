<?php

// Toggle favorito: exige login
if (!auth()) {
    flash()->put('error', 'Faça login para favoritar estratégias.');
    header('Location: /login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    abort(405, 'Método não permitido.');
}

$user_id = auth()->id;
$estrategia_id = (int)($_POST['estrategia_id'] ?? 0);
$redirect = $_POST['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? '/explore');

if (!$estrategia_id) {
    flash()->put('error', 'Estratégia inválida.');
    header('Location: ' . $redirect);
    exit();
}

try {
    $favorited = Favorite::toggle($user_id, $estrategia_id);
    flash()->put('message', $favorited ? 'Adicionada às favoritas.' : 'Removida das favoritas.');
} catch (Exception $e) {
    flash()->put('error', 'Erro ao atualizar favorito: ' . $e->getMessage());
}

header('Location: ' . $redirect);
exit();

?>