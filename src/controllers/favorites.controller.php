<?php

// Página Favoritas: exige login
if (!auth()) {
    flash()->put('error', 'Faça login para acessar suas favoritas.');
    header('Location: /login');
    exit();
}

// Parâmetros iguais ao Explore, com ordenação padrão "recentes" como em Minhas Estratégias
$search = $_REQUEST['pesquisar'] ?? '';
$order = $_REQUEST['ordenar'] ?? 'recentes';

$filter_agent = $_REQUEST['filtro_agente'] ?? '';
$filter_map = $_REQUEST['filtro_mapa'] ?? '';
$filter_category = $_REQUEST['filtro_categoria'] ?? '';

$agents = Agent::all();
$maps = Map::all();
$categories = ['defesa', 'ataque', 'pós plant', 'retake'];

// Paginação
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$user_id = auth()->id;

// Buscar favoritas paginadas com filtros
$estrategias = Favorite::listPaginated(
    $user_id,
    $per_page,
    $offset,
    $search,
    $filter_agent ?: null,
    $filter_map ?: null,
    $filter_category ?: null
);

// Total para paginação com filtros
$total = Favorite::countByUser($user_id, $search, $filter_agent ?: null, $filter_map ?: null, $filter_category ?: null);
$total_pages = max(1, (int)ceil($total / $per_page));

// Ordenação dinâmica conforme seleção do usuário (mesma lógica do Explore/MyStrategy)
if (is_array($estrategias)) {
    usort($estrategias, function($a, $b) use ($order) {
        switch ($order) {
            case 'mais_avaliadas':
                if ($a->ratings_count === $b->ratings_count) {
                    return $b->rating_average <=> $a->rating_average;
                }
                return $b->ratings_count <=> $a->ratings_count;
            case 'recentes':
                $aTime = strtotime($a->created_at);
                $bTime = strtotime($b->created_at);
                if ($aTime === $bTime) {
                    return $b->rating_average <=> $a->rating_average;
                }
                return $bTime <=> $aTime;
            case 'menos_estrelas':
                if ($a->rating_average === $b->rating_average) {
                    return $a->ratings_count <=> $b->ratings_count;
                }
                return $a->rating_average <=> $b->rating_average;
            case 'mais_estrelas':
            default:
                if ($a->rating_average === $b->rating_average) {
                    return $b->ratings_count <=> $a->ratings_count;
                }
                return $b->rating_average <=> $a->rating_average;
        }
    });
}

// Anotar status de favorito
if (is_array($estrategias)) {
    foreach ($estrategias as $e) {
        $e->is_favorite = true; // por definição, estão em favoritas
    }
}

view('app', compact('estrategias', 'search', 'order', 'agents', 'maps', 'categories', 'filter_agent', 'filter_map', 'filter_category', 'page', 'total_pages'), 'favorites');

?>