<?php

unset($_SESSION["flash_formData"]);

// Redireciona visitantes para login
if (!auth()) {
    flash()->put('error', 'Faça login para acessar Minhas estratégias.');
    header('Location: /login');
    exit();
}

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

// Buscar estratégias paginadas do usuário
$estrategias = Estrategia::filterPaginated(
    $search,
    $filter_agent ?: null,
    $filter_map ?: null,
    $filter_category ?: null,
    auth()->id,
    $per_page,
    $offset
);

// Total para paginação
$total = Estrategia::countFiltered($search, $filter_agent ?: null, $filter_map ?: null, $filter_category ?: null, auth()->id);
$total_pages = max(1, (int)ceil($total / $per_page));

// Ordenação dinâmica conforme seleção do usuário
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

view("app", compact('estrategias', 'search', 'order', 'agents', 'maps', 'categories', 'filter_agent', 'filter_map', 'filter_category', 'page', 'total_pages'), "myStrategy");
