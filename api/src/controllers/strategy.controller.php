<?php

// Permitir acesso ao detalhe para visitantes

$estrategia_id = $_GET['id'] ?? null;

$estrategia = Estrategia::get($estrategia_id);

$database = new Database(config('database')['database']);

// Paginação de avaliações (5 por página)
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

// Total de avaliações para paginação
$countStmt = $database->query(
    "SELECT COUNT(*) AS total FROM ratings WHERE estrategia_id = :estrategia_id",
    null,
    compact('estrategia_id')
);
$row = $countStmt ? $countStmt->fetch(PDO::FETCH_ASSOC) : null;
$total = $row ? (int)$row['total'] : 0;
$total_pages = max(1, (int)ceil($total / $per_page));

// Buscar avaliações paginadas e ordenadas por mais recentes
$ratings = $database->query(
    "SELECT 
        r.*, 
        u.name as user_name, 
        u.avatar as user_avatar, 
        u.elo as user_elo,
        (SELECT COUNT(*) FROM ratings countR WHERE countR.user_id = r.user_id) AS rated_movies
     FROM ratings r
     LEFT JOIN users u ON u.id = r.user_id
     WHERE r.estrategia_id = :estrategia_id
     ORDER BY r.created_at DESC
     LIMIT $per_page OFFSET $offset",
    Rating::class,
    compact('estrategia_id')
);

// Passar estratégia como 'movie' para o componente
$movie = $estrategia;

// Buscar autor da estratégia
$authorRows = (new User())->query('id = :id', ['id' => $estrategia->user_id]);
$author = is_array($authorRows) ? ($authorRows[0] ?? null) : $authorRows;

// Status de favorito para usuário autenticado
$is_favorite = auth() ? Favorite::isFavorite(auth()->id, $estrategia->id) : false;

view("app", compact('movie', 'ratings', 'author', 'is_favorite', 'page', 'total_pages'), "movie");
