<?php

declare(strict_types=1);

use App\Models\Favorite;
use App\Models\Strategy;
use App\Support\Auth;

if (!Auth::check()) {
    flash()->put('error', 'Faça login para favoritar estratégias.');
    redirect('/login');
}

$strategyId = filter_var($_POST['strategy_id'] ?? '', FILTER_VALIDATE_INT);
$redirectTo = $_POST['redirect'] ?? null;

if ($strategyId === false || $strategyId <= 0 || Strategy::find($strategyId) === null) {
    flash()->put('error', 'Estratégia não encontrada.');
    redirect_back(is_string($redirectTo) ? $redirectTo : null);
}

$isFavorite = Favorite::toggle((int) Auth::id(), $strategyId);

flash()->put('message', $isFavorite
    ? 'Estratégia adicionada às favoritas.'
    : 'Estratégia removida das favoritas.');

redirect_back(is_string($redirectTo) ? $redirectTo : null);
