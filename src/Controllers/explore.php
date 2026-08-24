<?php

declare(strict_types=1);

use App\Http\StrategyListing;
use App\Models\Strategy;
use App\Support\Auth;

// Visitantes podem explorar; criar, favoritar e avaliar exigem login.
$data = StrategyListing::build(
    fixedFilters: [],
    defaultOrder: Strategy::DEFAULT_ORDER,
    viewerId: Auth::id(),
);

view('app', $data, 'explore');
