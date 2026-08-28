<?php

declare(strict_types=1);

use App\Services\Storage;
use App\Services\UploadValidator;
use App\Support\Auth;

/**
 * Primeiro passo do upload direto: devolve uma URL assinada do Supabase
 * Storage para o navegador enviar o arquivo, sem esse pedido carregar o
 * binário - é só assim que um vídeo consegue passar pelo limite de ~4,5 MB
 * que a Vercel aplica a toda função serverless.
 *
 * Resposta em JSON, não HTML: quem chama isso é o JavaScript do formulário de
 * criação/edição de estratégia, via fetch().
 */

header('Content-Type: application/json; charset=utf-8');

/**
 * @param array<string, mixed> $data
 */
$respond = static function (int $status, array $data): never {
    http_response_code($status);
    echo json_encode($data);
    exit;
};

if (!Auth::check()) {
    $respond(401, ['error' => 'Faça login para enviar arquivos.']);
}

// O corpo vem como JSON (o JS manda application/json), não como um
// formulário tradicional - o PHP só popula $_POST automaticamente para
// application/x-www-form-urlencoded ou multipart/form-data.
$payload = json_decode((string) file_get_contents('php://input'), true);
$payload = is_array($payload) ? $payload : [];

$kind = (string) ($payload['kind'] ?? '');
$extension = strtolower((string) ($payload['extension'] ?? ''));

$storageKind = match ($kind) {
    'capa' => 'image',
    'video' => 'video',
    default => null,
};

if ($storageKind === null) {
    $respond(422, ['error' => 'Tipo de mídia inválido.']);
}

$allowedExtensions = $storageKind === 'image'
    ? UploadValidator::imageTypes()
    : UploadValidator::videoTypes();

// imageTypes()/videoTypes() mapeiam mime => extensão; o que chega aqui é a
// extensão, então a checagem é contra os valores, não as chaves.
if (!in_array($extension, array_values($allowedExtensions), true)) {
    $respond(422, ['error' => 'Extensão de arquivo não permitida.']);
}

$storage = Storage::disk();

if (!$storage->supportsDirectUpload()) {
    // Cai aqui só em ambiente local (SQLite/disco) - o formulário detecta essa
    // resposta e usa o upload tradicional, que nesse ambiente não tem o
    // limite de tamanho da Vercel para se preocupar.
    $respond(501, ['error' => 'Upload direto não é suportado neste ambiente.']);
}

$signed = $storage->createSignedUpload($storageKind, (int) Auth::id(), $extension);

$respond(200, [
    'upload_url' => $signed['upload_url'],
    'path' => $signed['path'],
]);
