<?php

declare(strict_types=1);

use App\Models\Strategy;
use App\Support\Auth;

if (!Auth::check()) {
    flash()->put('error', 'Faça login para acessar a moderação.');
    redirect('/login');
}

if (!Auth::isAdmin()) {
    abort(403, 'Você não tem permissão para acessar esta página.');
}

$page = max(1, (int) ($_GET['page'] ?? 1));

// Ordem 'antigas' primeiro: quem mandou faz mais tempo é atendido primeiro -
// a mesma lógica de qualquer fila.
$result = Strategy::paginate(
    ['statuses' => [Strategy::STATUS_PENDING], 'order' => 'antigas'],
    $page,
    20,
);

view('app', [
    'strategies' => $result['items'],
    'total' => $result['total'],
    'page' => $result['page'],
    'total_pages' => $result['pages'],
], 'admin-moderation');
