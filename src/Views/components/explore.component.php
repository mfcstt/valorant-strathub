<?php

declare(strict_types=1);

use App\Support\Auth;

$browser_route = '/explore';
$browser_title = 'Explorar estratégias';
$browser_empty_icon = 'ph ph-target';
$browser_empty_title = 'Ainda não há estratégias por aqui';
$browser_empty_message = 'Seja a primeira pessoa a publicar uma estratégia e ajudar a comunidade a subir de elo.';
$browser_show_create = Auth::check();

require __DIR__ . '/../partials/_strategy-browser.php';
