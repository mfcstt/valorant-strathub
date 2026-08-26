<?php

declare(strict_types=1);

use App\Models\Strategy;
use App\Support\Auth;

if (!Auth::check()) {
    flash()->put('error', 'Faça login para moderar estratégias.');
    redirect('/login');
}

if (!Auth::isAdmin()) {
    abort(403, 'Você não tem permissão para moderar estratégias.');
}

$strategyId = filter_var($_POST['strategy_id'] ?? '', FILTER_VALIDATE_INT);
$action = (string) ($_POST['action'] ?? '');
$note = trim((string) ($_POST['nota'] ?? ''));

if ($strategyId === false || $strategyId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
    flash()->put('error', 'Ação de moderação inválida.');
    redirect('/admin/moderacao');
}

if ($action === 'reject' && $note === '') {
    flash()->put('error', 'Explique o motivo da rejeição para a pessoa poder corrigir e reenviar.');
    redirect('/admin/moderacao');
}

$status = $action === 'approve' ? Strategy::STATUS_APPROVED : Strategy::STATUS_REJECTED;
$moderated = Strategy::moderate($strategyId, $status, $action === 'reject' ? $note : null);

if (!$moderated) {
    flash()->put('error', 'Estratégia não encontrada.');
    redirect('/admin/moderacao');
}

flash()->put(
    'message',
    $action === 'approve' ? 'Estratégia aprovada e publicada.' : 'Estratégia rejeitada. A pessoa vai ver o motivo e pode reenviar.',
);
redirect('/admin/moderacao');
