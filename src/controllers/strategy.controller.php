<?php

// Permitir acesso ao detalhe para visitantes

$estrategia_id = $_GET['id'] ?? null;

$estrategia = Estrategia::get($estrategia_id);

$database = new Database(config('database')['database']);

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

// Buscar autor da estratégia
$authorRows = (new User())->query('id = :id', ['id' => $estrategia->user_id]);
$author = is_array($authorRows) ? ($authorRows[0] ?? null) : $authorRows;

view("app", compact('movie', 'ratings', 'author'), "movie");
