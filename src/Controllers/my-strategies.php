<?php

declare(strict_types=1);

use App\Http\StrategyListing;
use App\Support\Auth;

if (!Auth::check()) {
    flash()->put('error', 'Faça login para acessar suas estratégias.');
    redirect('/login');
}

$userId = (int) Auth::id();

$data = StrategyListing::build(
    fixedFilters: ['user_id' => $userId],
    defaultOrder: 'recentes',
    viewerId: $userId,
);

view('app', $data, 'my-strategies');
