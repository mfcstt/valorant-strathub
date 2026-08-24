<?php

declare(strict_types=1);

use App\Models\Rating;
use App\Models\Strategy;
use App\Support\Auth;
use App\Support\Validation;

if (!Auth::check()) {
    flash()->put('error', 'Faça login para avaliar estratégias.');
    redirect('/login');
}

$strategyId = filter_var($_POST['strategy_id'] ?? '', FILTER_VALIDATE_INT);

if ($strategyId === false || $strategyId <= 0) {
    abort(404, 'Estratégia não encontrada.');
}

$strategy = Strategy::find($strategyId);

if ($strategy === null) {
    abort(404, 'Esta estratégia não existe ou foi removida.');
}

$userId = (int) Auth::id();

// Avaliar a própria estratégia inflaria a nota do autor e distorceria a
// ordenação de "Mais estrelas" — a regra que sustenta a página inicial.
if ((int) $strategy->user_id === $userId) {
    flash()->put('error', 'Você não pode avaliar a sua própria estratégia.');
    redirect('/strategy?id=' . $strategyId);
}

$validation = Validation::validate([
    'avaliacao' => ['required', 'integer', 'between:' . Rating::MIN . ',' . Rating::MAX],
    'comentario' => ['required', 'min:3', 'max:300'],
], $_POST);

if ($validation->fails()) {
    $validation->flashErrors();
    flash()->put('formData', ['comentario' => (string) ($_POST['comentario'] ?? '')]);
    redirect('/strategy?id=' . $strategyId);
}

$wasUpdate = Rating::upsert(
    $userId,
    $strategyId,
    (int) $_POST['avaliacao'],
    trim((string) $_POST['comentario']),
);

flash()->put('message', $wasUpdate
    ? 'Sua avaliação foi atualizada.'
    : 'Avaliação publicada. Obrigado por contribuir!');

redirect('/strategy?id=' . $strategyId);
