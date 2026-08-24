<?php

declare(strict_types=1);

use App\Support\Auth;

Auth::logout();

flash()->put('message', 'Você saiu da sua conta.');
redirect('/login');
