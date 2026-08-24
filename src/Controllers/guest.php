<?php

declare(strict_types=1);

use App\Support\Auth;

Auth::enterGuestMode();

flash()->put(
    'message',
    'Você está navegando como visitante. Faça login para criar, favoritar ou avaliar estratégias.'
);

redirect('/explore');
