<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit();
}

$user_id = auth()->id;
$estrategia_id = $_POST['estrategia_id'] ?? null;
$rating = $_POST['avaliacao'] ?? null;
$comment = $_POST['comentario'] ?? '';

$validation = Validation::validate([
    'avaliacao' => ['required'],
    'comentario' => ['required']
], $_POST);

if ($validation->notPassed()) {
    flash()->put('formData', $_POST);
    header('Location: /strategy?id=' . $estrategia_id);
    exit();
}

$database = new Database(config('database')['database']);

// Verificar se já existe uma avaliação
$existing = $database->query(
    "SELECT id FROM ratings WHERE user_id = :user_id AND estrategia_id = :estrategia_id",
    null,
    compact('user_id', 'estrategia_id')
);

$isUpdate = $existing->fetch() !== false;

$database->query(
    "INSERT INTO ratings (user_id, estrategia_id, rating, comment)
    VALUES (:user_id, :estrategia_id, :rating, :comment)
    ON CONFLICT (user_id, estrategia_id) 
    DO UPDATE SET 
        rating = EXCLUDED.rating,
        comment = EXCLUDED.comment,
        created_at = CURRENT_TIMESTAMP",
    null,
    compact('user_id', 'estrategia_id', 'rating', 'comment')
);

$message = $isUpdate ? 'Avaliação atualizada com sucesso!' : 'Avaliação realizada com sucesso!';
flash()->put('message', $message);
header('Location: /strategy?id=' . $estrategia_id);
exit();
