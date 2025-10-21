<?php

unset($_SESSION["flash_formData"]);

// Redireciona visitantes para login
if (!auth()) {
    flash()->put('error', 'Faça login para acessar Minhas estratégias.');
    header('Location: /login');
    exit();
}

$search = $_REQUEST['pesquisar'] ?? '';
$order = $_REQUEST['ordenar'] ?? 'mais_estrelas';

$estrategias = $search ? Estrategia::all($search) : Estrategia::myEstrategias(auth()->id);

// Ordenação dinâmica conforme seleção do usuário
if (is_array($estrategias)) {
    usort($estrategias, function($a, $b) use ($order) {
        switch ($order) {
            case 'mais_avaliadas':
                // Mais avaliações primeiro; desempate por maior média
                if ($a->ratings_count === $b->ratings_count) {
                    return $b->rating_average <=> $a->rating_average;
                }
                return $b->ratings_count <=> $a->ratings_count;
            case 'recentes':
                // Mais recentes primeiro; desempate por maior média
                $aTime = strtotime($a->created_at);
                $bTime = strtotime($b->created_at);
                if ($aTime === $bTime) {
                    return $b->rating_average <=> $a->rating_average;
                }
                return $bTime <=> $aTime;
            case 'menos_estrelas':
                // Menor média primeiro; desempate por menos avaliações
                if ($a->rating_average === $b->rating_average) {
                    return $a->ratings_count <=> $b->ratings_count;
                }
                return $a->rating_average <=> $b->rating_average;
            case 'mais_estrelas':
            default:
                // Maior média primeiro; desempate por mais avaliações
                if ($a->rating_average === $b->rating_average) {
                    return $b->ratings_count <=> $a->ratings_count;
                }
                return $b->rating_average <=> $a->rating_average;
        }
    });
}

view("app", compact('estrategias', 'search', 'order'), "myStrategy");
