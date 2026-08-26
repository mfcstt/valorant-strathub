<?php

declare(strict_types=1);

use App\Models\Image;
use App\Models\Strategy;
use App\Models\Video;
use App\Services\Storage;
use App\Support\Auth;
use App\Support\Config;

if (!Auth::check()) {
    flash()->put('error', 'Faça login para excluir estratégias.');
    redirect('/login');
}

$strategyId = filter_var($_POST['strategy_id'] ?? '', FILTER_VALIDATE_INT);

if ($strategyId === false || $strategyId <= 0) {
    flash()->put('error', 'Estratégia inválida.');
    redirect('/my-strategies');
}

$userId = (int) Auth::id();
$strategy = Strategy::find($strategyId);

if ($strategy === null) {
    flash()->put('error', 'Estratégia não encontrada.');
    redirect('/my-strategies');
}

if ((int) $strategy->user_id !== $userId) {
    abort(403, 'Você não tem permissão para excluir esta estratégia.');
}

$coverImage = $strategy->cover_image_id !== null
    ? Image::find((int) $strategy->cover_image_id)
    : null;

$video = $strategy->video_id !== null
    ? Video::find((int) $strategy->video_id)
    : null;

// A estratégia sai do banco primeiro, dentro de uma transação. As avaliações e
// favoritas vão com ela por ON DELETE CASCADE.
if (!Strategy::deleteOwnedBy($strategyId, $userId)) {
    flash()->put('error', 'Não foi possível excluir a estratégia.');
    redirect('/my-strategies');
}

// A limpeza da mídia acontece depois e não pode derrubar a operação: se o
// Supabase estiver fora do ar, o pior resultado é um arquivo órfão no bucket -
// bem melhor que devolver erro para uma exclusão que já aconteceu.
try {
    $storage = Storage::disk();

    if ($coverImage !== null) {
        $storage->deleteImage((string) $coverImage->file_path);
        $coverImage->delete();
    }

    if ($video !== null) {
        $storage->deleteVideo((string) $video->file_path);
        $video->delete();
    }
} catch (Throwable $e) {
    error_log('[strathub] limpeza de mídia após exclusão falhou: ' . $e->getMessage());

    if (Config::isDebug()) {
        flash()->put('error', 'Estratégia excluída, mas a mídia não foi removida do storage.');
    }
}

flash()->put('message', 'Estratégia excluída com sucesso.');
redirect('/my-strategies');
