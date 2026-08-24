<?php

declare(strict_types=1);

use App\Models\Agent;
use App\Models\Map;
use App\Models\Strategy;
use App\Services\Storage;
use App\Services\UploadValidator;
use App\Support\Auth;
use App\Support\Validation;

if (!Auth::check()) {
    flash()->put('error', 'Faça login para cadastrar uma estratégia.');
    redirect('/login');
}

if (!is_post()) {
    view('app', ['agents' => Agent::all(), 'maps' => Map::all()], 'strategy-create');

    return;
}

$userId = (int) Auth::id();

$title = trim((string) ($_POST['titulo'] ?? ''));
$category = mb_strtolower(trim((string) ($_POST['categoria'] ?? '')));
$description = trim((string) ($_POST['descricao'] ?? ''));
$agentId = filter_var($_POST['agente'] ?? '', FILTER_VALIDATE_INT) ?: null;
$mapId = filter_var($_POST['mapa'] ?? '', FILTER_VALIDATE_INT) ?: null;

$coverFile = $_FILES['capa'] ?? null;
$videoFile = $_FILES['video'] ?? null;

$hasCover = UploadValidator::isSuccessful($coverFile);
$hasVideo = UploadValidator::isSuccessful($videoFile);

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

// Uma estratégia precisa de pelo menos uma mídia para ser útil a quem lê.
if (!$hasCover && !$hasVideo) {
    $message = 'Envie uma imagem de capa ou um vídeo para publicar a estratégia.';

    // Se houve tentativa de envio que falhou, mostra o motivo real em vez de
    // apenas dizer que falta o arquivo.
    foreach (['capa' => $coverFile, 'video' => $videoFile] as $field => $file) {
        if (UploadValidator::wasUploaded($file) && !UploadValidator::isSuccessful($file)) {
            $validation->addError($field, UploadValidator::describeError((int) $file['error']));
        }
    }

    if (!isset($validation->errors()['capa']) && !isset($validation->errors()['video'])) {
        $validation->addError('capa', $message);
    }
}

/**
 * Devolve a pessoa ao formulário preservando o que ela digitou.
 */
$backToForm = static function (Validation $validation) use ($title, $category, $description, $agentId, $mapId): never {
    $validation->flashErrors();
    flash()->put('formData', [
        'titulo' => $title,
        'categoria' => $category,
        'descricao' => $description,
        'agente' => $agentId,
        'mapa' => $mapId,
    ]);

    redirect('/strategy-create');
};

if ($validation->fails()) {
    $backToForm($validation);
}

$storage = Storage::disk();
$coverImageId = null;
$videoId = null;

if ($hasCover) {
    $result = $storage->uploadImage((array) $coverFile, $userId);

    if (!$result->ok) {
        $validation->addError('capa', (string) $result->error);
        $backToForm($validation);
    }

    $coverImageId = $result->id();
}

if ($hasVideo) {
    $result = $storage->uploadVideo((array) $videoFile, $userId);

    if (!$result->ok) {
        $validation->addError('video', (string) $result->error);
        $backToForm($validation);
    }

    $videoId = $result->id();
}

$strategyId = Strategy::create([
    'title' => $title,
    'category' => $category,
    'description' => $description,
    'cover_image_id' => $coverImageId,
    'video_id' => $videoId,
    'user_id' => $userId,
    'agent_id' => $agentId,
    'map_id' => $mapId,
]);

flash()->put('message', 'Estratégia publicada com sucesso!');
redirect('/strategy?id=' . $strategyId);
