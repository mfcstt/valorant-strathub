<?php

declare(strict_types=1);

use App\Services\Storage;
use App\Support\Auth;

/**
 * Apaga um upload direto que ficou pra trás no Storage: a pessoa escolheu um
 * arquivo, ele já subiu pro Supabase (ver upload-sign.php), mas antes de
 * publicar ela trocou de novo - o primeiro arquivo nunca chega a ser
 * referenciado por nenhuma estratégia, então nada nunca o apagaria sozinho.
 *
 * Chamado pelo JS em best-effort logo depois que o upload de substituição é
 * confirmado (ver cleanupOrphanedUpload() em appViewScripts.js) - uma falha
 * aqui não afeta o restante do formulário, só deixa um arquivo órfão pra
 * trás, exatamente como já acontecia antes deste endpoint existir.
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
    $respond(401, ['error' => 'Faça login para gerenciar arquivos.']);
}

$payload = json_decode((string) file_get_contents('php://input'), true);
$payload = is_array($payload) ? $payload : [];

$kind = (string) ($payload['kind'] ?? '');
$path = (string) ($payload['path'] ?? '');

$storageKind = match ($kind) {
    'capa' => 'image',
    'video' => 'video',
    default => null,
};

if ($storageKind === null) {
    $respond(422, ['error' => 'Tipo de mídia inválido.']);
}

$userId = (int) Auth::id();

// Mesmo formato exigido em SupabaseStorageService::finalizeUpload(): só
// aceita apagar um objeto que já bate com o padrão gerado pelo próprio
// createSignedUpload() para ESTE usuário - impede que alguém apague o
// arquivo de outra pessoa só adivinhando (ou lendo) um path alheio.
if (!preg_match('#^user_' . $userId . '/[a-f0-9]{32}\.[a-z0-9]+$#', $path)) {
    $respond(200, ['ok' => true]);
}

$storage = Storage::disk();

if ($storageKind === 'image') {
    $storage->deleteImage($path);
} else {
    $storage->deleteVideo($path);
}

$respond(200, ['ok' => true]);
