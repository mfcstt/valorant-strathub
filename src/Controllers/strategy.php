<?php

declare(strict_types=1);

use App\Models\Rating;
use App\Models\Strategy;
use App\Models\User;
use App\Support\Auth;

$strategyId = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if ($strategyId === false || $strategyId <= 0) {
    abort(404, 'Estratégia não encontrada.');
}

$viewerId = Auth::id();
$strategy = Strategy::find($strategyId, $viewerId);

// Antes o código seguia direto para `$strategy->user_id`, e um id inexistente
// virava erro 500 em vez de 404.
if ($strategy === null) {
    abort(404, 'Esta estratégia não existe ou foi removida.');
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$ratings = Rating::paginateForStrategy($strategyId, $page, 5);

$author = User::find((int) $strategy->user_id);

$myRating = $viewerId !== null
    ? Rating::findByUserAndStrategy($viewerId, $strategyId)
    : null;

view('app', [
    'strategy' => $strategy,
    'ratings' => $ratings['items'],
    'ratings_total' => $ratings['total'],
    'page' => $ratings['page'],
    'total_pages' => $ratings['pages'],
    'author' => $author,
    'my_rating' => $myRating,
], 'strategy-detail');
