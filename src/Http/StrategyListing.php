<?php

declare(strict_types=1);

namespace App\Http;

use App\Models\Agent;
use App\Models\Map;
use App\Models\Strategy;

/**
 * Lê os filtros da requisição e monta os dados de uma página de listagem.
 *
 * Explorar, Minhas estratégias e Favoritas tinham o mesmo bloco de parsing de
 * filtros e a mesma closure de `usort` de 25 linhas copiada três vezes — e as
 * cópias já haviam divergido entre si. Agora as três páginas diferem apenas nos
 * filtros fixos que passam para cá.
 */
final class StrategyListing
{
    private const PER_PAGE = 10;

    /**
     * Monta os dados da listagem.
     *
     * @param  array<string, mixed> $fixedFilters filtros que a página impõe
     *                                            (por exemplo o autor, em
     *                                            "Minhas estratégias")
     * @param  string               $defaultOrder ordenação quando a requisição
     *                                            não especifica
     * @return array<string, mixed> pronto para `compact`/`view`
     */
    public static function build(
        array $fixedFilters = [],
        string $defaultOrder = Strategy::DEFAULT_ORDER,
        ?int $viewerId = null,
    ): array {
        $search = trim((string) ($_GET['pesquisar'] ?? ''));
        $order = Strategy::normalizeOrder(
            isset($_GET['ordenar']) ? (string) $_GET['ordenar'] : $defaultOrder
        );

        $filterAgent = self::intOrNull($_GET['filtro_agente'] ?? null);
        $filterMap = self::intOrNull($_GET['filtro_mapa'] ?? null);
        $filterCategory = self::categoryOrNull($_GET['filtro_categoria'] ?? null);

        $page = max(1, (int) ($_GET['page'] ?? 1));

        $result = Strategy::paginate(
            [
                ...$fixedFilters,
                'search' => $search,
                'agent_id' => $filterAgent,
                'map_id' => $filterMap,
                'category' => $filterCategory,
                'order' => $order,
            ],
            $page,
            self::PER_PAGE,
            $viewerId,
        );

        return [
            'strategies' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'total_pages' => $result['pages'],
            'search' => $search,
            'order' => $order,
            'agents' => Agent::all(),
            'maps' => Map::all(),
            'categories' => Strategy::CATEGORIES,
            'filter_agent' => $filterAgent,
            'filter_map' => $filterMap,
            'filter_category' => $filterCategory,
            'has_active_filters' => $filterAgent !== null
                || $filterMap !== null
                || $filterCategory !== null,
        ];
    }

    private static function intOrNull(mixed $value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $int = filter_var((string) $value, FILTER_VALIDATE_INT);

        return ($int === false || $int <= 0) ? null : $int;
    }

    /**
     * Só aceita categorias conhecidas: um valor livre viraria um filtro que
     * nunca casa e uma página vazia sem explicação.
     */
    private static function categoryOrNull(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $category = mb_strtolower(trim((string) $value));

        return in_array($category, Strategy::CATEGORIES, true) ? $category : null;
    }
}
