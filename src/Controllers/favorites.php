<?php

declare(strict_types=1);

use App\Http\StrategyListing;
use App\Support\Auth;

if (!Auth::check()) {
    flash()->put('error', 'Faça login para acessar suas favoritas.');
    redirect('/login');
}

$userId = (int) Auth::id();

$data = StrategyListing::build(
    fixedFilters: ['favorited_by' => $userId],
    defaultOrder: 'recentes',
    viewerId: $userId,
);

view('app', $data, 'favorites');
