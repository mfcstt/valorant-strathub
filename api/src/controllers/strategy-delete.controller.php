<?php

// Excluir estratégia (somente dono), mesmo com avaliações

if (!auth()) {
    flash()->put('error', 'Faça login para excluir estratégias.');
    header('Location: /login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    abort(405, 'Método não permitido.');
}

$estrategia_id = $_POST['estrategia_id'] ?? null;

if (!$estrategia_id) {
    flash()->put('error', 'ID da estratégia não informado.');
    header('Location: /myStrategy');
    exit();
}

$estrategia = Estrategia::get($estrategia_id);

if (!$estrategia) {
    flash()->put('error', 'Estratégia não encontrada.');
    header('Location: /myStrategy');
    exit();
}

if ($estrategia->user_id !== auth()->id) {
    abort(403, 'Você não tem permissão para excluir esta estratégia.');
}

$database = new Database(config('database')['database']);
$storage = new SupabaseStorageService();

try {
    // Remover mídia associada (opcional, para evitar órfãos)
    if (!empty($estrategia->cover_image_id)) {
        $image = Image::get($estrategia->cover_image_id);
        if ($image) {
            if (!empty($image->file_path)) {
                $storage->deleteImage($image->file_path);
            }
            $image->delete();
        }
    }

    if (!empty($estrategia->video_id)) {
        $videoRows = (new Video())->query('id = :id', ['id' => $estrategia->video_id]);
        $video = is_array($videoRows) ? ($videoRows[0] ?? null) : null;
        if ($video) {
            if (!empty($video->file_path)) {
                $storage->deleteVideo($video->file_path);
            }
            $video->delete();
        }
    }

    // Excluir estratégia; avaliações serão removidas por ON DELETE CASCADE
    $database->query(
        'DELETE FROM estrategias WHERE id = :id AND user_id = :user_id',
        null,
        ['id' => $estrategia->id, 'user_id' => auth()->id]
    );

    flash()->put('message', 'Estratégia excluída com sucesso.');
    header('Location: /myStrategy');
    exit();
} catch (Exception $e) {
    flash()->put('error', 'Erro ao excluir estratégia: ' . $e->getMessage());
    header('Location: /myStrategy');
    exit();
}