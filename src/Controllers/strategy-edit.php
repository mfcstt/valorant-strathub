<?php

declare(strict_types=1);

use App\Models\Agent;
use App\Models\Image;
use App\Models\Map;
use App\Models\Strategy;
use App\Models\Video;
use App\Services\Storage;
use App\Services\UploadValidator;
use App\Support\Auth;
use App\Support\Validation;

if (!Auth::check()) {
    flash()->put('error', 'Faça login para editar uma estratégia.');
    redirect('/login');
}

$userId = (int) Auth::id();
$strategyId = filter_var($_GET['id'] ?? $_POST['id'] ?? '', FILTER_VALIDATE_INT);

if ($strategyId === false || $strategyId <= 0) {
    flash()->put('error', 'Estratégia inválida.');
    redirect('/my-strategies');
}

$strategy = Strategy::find($strategyId);

if ($strategy === null || (int) $strategy->user_id !== $userId) {
    abort(403, 'Você não tem permissão para editar esta estratégia.');
}

if (!is_post()) {
    // Pré-preenche o formulário com os dados atuais da estratégia — só quando
    // não há dados de uma tentativa de envio anterior ainda na sessão (o
    // redirecionamento de uma validação que falhou já deixou os próprios lá,
    // e sobrescrever perderia o que a pessoa acabou de digitar).
    if (!flash()->has('formData')) {
        flash()->put('formData', [
            'titulo' => (string) $strategy->title,
            'categoria' => (string) $strategy->category,
            'descricao' => (string) $strategy->description,
            'agente' => $strategy->agent_id !== null ? (string) $strategy->agent_id : null,
            'mapa' => $strategy->map_id !== null ? (string) $strategy->map_id : null,
        ]);
    }

    view('app', ['agents' => Agent::all(), 'maps' => Map::all(), 'strategy' => $strategy], 'strategy-edit');

    return;
}

$title = trim((string) ($_POST['titulo'] ?? ''));
$category = mb_strtolower(trim((string) ($_POST['categoria'] ?? '')));
$description = trim((string) ($_POST['descricao'] ?? ''));
$agentId = filter_var($_POST['agente'] ?? '', FILTER_VALIDATE_INT) ?: null;
$mapId = filter_var($_POST['mapa'] ?? '', FILTER_VALIDATE_INT) ?: null;

$coverFile = $_FILES['capa'] ?? null;
$videoFile = $_FILES['video'] ?? null;

// Trocar mídia é opcional na edição: quem não anexa um arquivo novo mantém o
// que já estava publicado. Diferente da criação, não existe checagem de "pelo
// menos uma mídia" aqui — a estratégia já tinha ao menos uma pra ter sido
// publicada, e essa invariante nunca é quebrada por uma edição.
$hasNewCover = UploadValidator::isSuccessful($coverFile);
$hasNewVideo = UploadValidator::isSuccessful($videoFile);

$validation = Validation::validate([
    'titulo' => ['required', 'min:3', 'max:100'],
    'categoria' => ['required', 'in:' . implode(',', Strategy::CATEGORIES)],
    'descricao' => ['required', 'min:10', 'max:500'],
    'agente' => ['required', 'integer'],
    'mapa' => ['required', 'integer'],
], [...$_POST, 'categoria' => $category]);

if ($agentId !== null && Agent::find($agentId) === null) {
    $validation->addError('agente', 'Selecione um agente válido.');
}

if ($mapId !== null && Map::find($mapId) === null) {
    $validation->addError('mapa', 'Selecione um mapa válido.');
}

foreach (['capa' => $coverFile, 'video' => $videoFile] as $field => $file) {
    if (UploadValidator::wasUploaded($file) && !UploadValidator::isSuccessful($file)) {
        $validation->addError($field, UploadValidator::describeError((int) $file['error']));
    }
}

/**
 * Devolve a pessoa ao formulário preservando o que ela digitou.
 */
$backToForm = static function (Validation $validation) use ($title, $category, $description, $agentId, $mapId, $strategyId): never {
    $validation->flashErrors();
    flash()->put('formData', [
        'titulo' => $title,
        'categoria' => $category,
        'descricao' => $description,
        'agente' => $agentId,
        'mapa' => $mapId,
    ]);

    redirect('/strategy-edit?id=' . $strategyId);
};

if ($validation->fails()) {
    $backToForm($validation);
}

$storage = Storage::disk();

$coverImageId = $strategy->cover_image_id;
$videoId = $strategy->video_id;
$oldCover = null;
$oldVideo = null;

if ($hasNewCover) {
    $coverResult = $storage->uploadImage((array) $coverFile, $userId);

    if (!$coverResult->ok) {
        $validation->addError('capa', (string) $coverResult->error);
        $backToForm($validation);
    }

    $oldCover = $coverImageId !== null ? Image::find((int) $coverImageId) : null;
    $coverImageId = $coverResult->id();
}

if ($hasNewVideo) {
    $videoResult = $storage->uploadVideo((array) $videoFile, $userId);

    if (!$videoResult->ok) {
        $validation->addError('video', (string) $videoResult->error);

        // A capa nova pode ter subido com sucesso antes do vídeo falhar — sem
        // esta limpeza ela ficaria órfã, já que updateOwnedBy() nunca é
        // alcançado neste caminho e a capa antiga continua sendo a "oficial".
        if ($hasNewCover && isset($coverResult) && $coverResult->file !== null) {
            try {
                $storage->deleteImage((string) $coverResult->file->file_path);
                $coverResult->file->delete();
            } catch (\Throwable $e) {
                error_log('[strathub] falha ao limpar capa órfã após vídeo inválido (edição): ' . $e->getMessage());
            }
        }

        $backToForm($validation);
    }

    $oldVideo = $videoId !== null ? Video::find((int) $videoId) : null;
    $videoId = $videoResult->id();
}

$updated = Strategy::updateOwnedBy($strategyId, $userId, [
    'title' => $title,
    'category' => $category,
    'description' => $description,
    'agent_id' => $agentId,
    'map_id' => $mapId,
    'cover_image_id' => $coverImageId,
    'video_id' => $videoId,
]);

if (!$updated) {
    flash()->put('error', 'Não foi possível salvar as alterações.');
    redirect('/strategy-edit?id=' . $strategyId);
}

// A mídia antiga só é apagada do Storage depois que a troca já está salva no
// banco — assim uma falha aqui não deixa a estratégia num estado inconsistente,
// só um arquivo órfão (melhor esforço, igual ao resto do app).
foreach ([[$oldCover, 'deleteImage'], [$oldVideo, 'deleteVideo']] as [$file, $method]) {
    if ($file === null) {
        continue;
    }

    try {
        $storage->{$method}((string) $file->file_path);
        $file->delete();
    } catch (\Throwable $e) {
        error_log('[strathub] falha ao apagar mídia antiga após edição: ' . $e->getMessage());
    }
}

flash()->put('message', 'Estratégia atualizada! Como o conteúdo mudou, ela volta pra fila de revisão antes de aparecer pra outras pessoas.');
redirect('/strategy?id=' . $strategyId);
