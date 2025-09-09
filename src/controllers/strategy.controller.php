<?php

if (!auth()) {
    abort(403, 'Você precisa estar logado para acessar essa página.');
}

$estrategia_id = $_GET['id'] ?? null;

$estrategia = Estrategia::get($estrategia_id);

$database = new Database(config('database'));

// Buscar avaliações
$ratings = $database->query(
    "SELECT 
        r.*,
        u.name as user_name,
        u.avatar as user_avatar,
        (SELECT COUNT(*) FROM ratings countR WHERE countR.user_id = r.user_id) AS rated_movies
     FROM ratings r
     LEFT JOIN users u ON u.id = r.user_id
     WHERE r.estrategia_id = :estrategia_id",
    Rating::class,
    compact('estrategia_id')
);

// Passar estratégia como 'movie' para o componente
$movie = $estrategia;
view("app", compact('movie', 'ratings'), "movie");
