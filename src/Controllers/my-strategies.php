<?php

declare(strict_types=1);

use App\Http\StrategyListing;
use App\Models\Strategy;
use App\Support\Auth;

if (!Auth::check()) {
    flash()->put('error', 'Faça login para acessar suas estratégias.');
    redirect('/login');
}

$userId = (int) Auth::id();

// Aqui é o único lugar onde a pessoa vê as próprias estratégias pendentes ou
// rejeitadas - em qualquer outra listagem, só o que já foi aprovado aparece.
$data = StrategyListing::build(
    fixedFilters: ['user_id' => $userId],
    defaultOrder: 'recentes',
    viewerId: $userId,
    statuses: Strategy::ALL_STATUSES,
);

view('app', $data, 'my-strategies');
