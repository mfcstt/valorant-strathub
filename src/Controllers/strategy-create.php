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
$coverResult = null;
$videoResult = null;

if ($hasCover) {
    $coverResult = $storage->uploadImage((array) $coverFile, $userId);

    if (!$coverResult->ok) {
        $validation->addError('capa', (string) $coverResult->error);
        $backToForm($validation);
    }
}

if ($hasVideo) {
    $videoResult = $storage->uploadVideo((array) $videoFile, $userId);

    if (!$videoResult->ok) {
        $validation->addError('video', (string) $videoResult->error);

        // A capa pode ter subido com sucesso antes do vídeo falhar. Sem esta
        // limpeza, ela ficaria órfã — um arquivo público no Storage e uma
        // linha em `images` sem nenhuma estratégia apontando para ela, já
        // que Strategy::create() nunca é alcançado neste caminho.
        if ($coverResult !== null && $coverResult->file !== null) {
            try {
                $storage->deleteImage((string) $coverResult->file->file_path);
                $coverResult->file->delete();
            } catch (\Throwable $e) {
                error_log('[strathub] falha ao limpar capa órfã após vídeo inválido: ' . $e->getMessage());
            }
        }

        $backToForm($validation);
    }
}

$strategyId = Strategy::create([
    'title' => $title,
    'category' => $category,
    'description' => $description,
    'cover_image_id' => $coverResult?->id(),
    'video_id' => $videoResult?->id(),
    'user_id' => $userId,
    'agent_id' => $agentId,
    'map_id' => $mapId,
]);

flash()->put('message', 'Estratégia enviada! Como o projeto ainda não tem moderação automática, ela passa por uma revisão manual antes de aparecer pra outras pessoas — isso deve levar pouco tempo.');
redirect('/strategy?id=' . $strategyId);
