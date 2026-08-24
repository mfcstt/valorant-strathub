<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Config;
use App\Support\Database;

/**
 * Uma estratégia publicada por um usuário.
 *
 * ## Ordenação
 *
 * A versão anterior paginava no SQL (`ORDER BY created_at`) e só então reordenava
 * em PHP com `usort` — sobre os 10 itens já trazidos. O resultado é que "Mais
 * estrelas" ordenava um recorte arbitrário: a estratégia mais bem avaliada do
 * site podia nunca aparecer no topo. Aqui a ordenação vive no `ORDER BY`, então
 * `LIMIT`/`OFFSET` recortam a lista já ordenada.
 *
 * ## Favoritas
 *
 * `is_favorite` vinha de `Favorite::isFavorite()` chamado num laço, uma consulta
 * por card. Agora é uma subconsulta `EXISTS` na própria listagem.
 */
final class Strategy
{
    public mixed $id = null;
    public mixed $title = null;
    public mixed $category = null;
    public mixed $description = null;
    public mixed $cover_image_id = null;
    public mixed $cover_image_url = null;
    public mixed $video_id = null;
    public mixed $video_url = null;
    public mixed $video_duration = null;
    public mixed $user_id = null;
    public mixed $author_name = null;
    public mixed $agent_id = null;
    public mixed $agent_name = null;
    public mixed $agent_photo = null;
    public mixed $map_id = null;
    public mixed $map_name = null;
    public mixed $created_at = null;
    public mixed $updated_at = null;
    public mixed $rating_average = 0;
    public mixed $ratings_count = 0;
    public mixed $is_favorite = 0;

    public const CATEGORIES = ['defesa', 'ataque', 'pós plant', 'retake'];

    /**
     * Ordenações aceitas, mapeadas para expressões SQL.
     *
     * A allowlist é o que permite interpolar a cláusula direto na consulta:
     * o valor vem da requisição, mas só pode ser uma das chaves abaixo.
     *
     * @var array<string, string>
     */
    private const ORDERS = [
        'mais_estrelas' => 'COALESCE(AVG(r.rating), 0) DESC, COUNT(r.id) DESC, e.id DESC',
        'menos_estrelas' => 'COALESCE(AVG(r.rating), 0) ASC, COUNT(r.id) ASC, e.id DESC',
        'mais_avaliadas' => 'COUNT(r.id) DESC, COALESCE(AVG(r.rating), 0) DESC, e.id DESC',
        'recentes' => 'e.created_at DESC, e.id DESC',
        'antigas' => 'e.created_at ASC, e.id ASC',
    ];

    public const DEFAULT_ORDER = 'mais_estrelas';

    /**
     * @return list<string>
     */
    public static function orderOptions(): array
    {
        return array_keys(self::ORDERS);
    }

    public function isFavorite(): bool
    {
        return (bool) $this->is_favorite;
    }

    public function ratingAverage(): float
    {
        return round((float) $this->rating_average, 1);
    }

    public function ratingsCount(): int
    {
        return (int) $this->ratings_count;
    }

    /**
     * Busca uma estratégia pelo id.
     *
     * @param int|null $viewerId usuário que está olhando, para resolver `is_favorite`
     */
    public static function find(int $id, ?int $viewerId = null): ?self
    {
        $result = self::query(
            ['id' => $id],
            $viewerId,
            self::resolveOrder(null),
            1,
            0,
        );

        return $result[0] ?? null;
    }

    /**
     * Listagem paginada com filtros.
     *
     * @param array{
     *     search?: string,
     *     agent_id?: int|string|null,
     *     map_id?: int|string|null,
     *     category?: string|null,
     *     user_id?: int|null,
     *     favorited_by?: int|null,
     *     order?: string
     * } $filters
     *
     * @return array{items: list<self>, total: int, page: int, pages: int, per_page: int}
     */
    public static function paginate(array $filters, int $page = 1, int $perPage = 10, ?int $viewerId = null): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(60, $perPage));

        $total = self::count($filters);
        $pages = max(1, (int) ceil($total / $perPage));

        // Uma página fora do intervalo não deve devolver lista vazia: prender o
        // valor à última página é o comportamento que a pessoa espera ao editar
        // a URL ou ao apagar o último item de uma página.
        $page = min($page, $pages);

        $items = self::query(
            $filters,
            $viewerId,
            self::resolveOrder($filters['order'] ?? null),
            $perPage,
            ($page - 1) * $perPage,
        );

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'per_page' => $perPage,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     */
    public static function count(array $filters): int
    {
        [$where, $params, $joins] = self::conditions($filters);

        $sql = 'SELECT COUNT(DISTINCT e.id) FROM strategies e '
            . 'LEFT JOIN agents a ON a.id = e.agent_id '
            . 'LEFT JOIN maps m ON m.id = e.map_id '
            . $joins
            . ' WHERE ' . $where;

        return (int) Database::connection()->scalar($sql, $params);
    }

    public static function normalizeOrder(?string $order): string
    {
        return self::resolveOrderKey($order);
    }

    /**
     * @param  array<string, mixed> $filters
     * @return list<self>
     */
    private static function query(
        array $filters,
        ?int $viewerId,
        string $orderBy,
        int $limit,
        int $offset,
    ): array {
        [$where, $params, $joins] = self::conditions($filters);

        // Os prefixos vêm da configuração (e variam com o driver de storage),
        // e entram como parâmetro vinculado em vez de interpolados no SQL — era
        // o único lugar da consulta onde um valor de configuração era concatenado
        // direto na string.
        $params['cover_prefix'] = (string) Config::get('storage.image_prefix', '');
        $params['video_prefix'] = (string) Config::get('storage.video_prefix', '');

        if ($viewerId !== null) {
            $favoriteExpression = 'CASE WHEN EXISTS ('
                . 'SELECT 1 FROM favorites vf WHERE vf.strategy_id = e.id AND vf.user_id = :viewer_id'
                . ') THEN 1 ELSE 0 END';
            $params['viewer_id'] = $viewerId;
        } else {
            $favoriteExpression = '0';
        }

        // LIMIT/OFFSET entram como inteiros já convertidos: alguns drivers não
        // aceitam esses dois como parâmetro vinculado.
        $limit = max(1, $limit);
        $offset = max(0, $offset);

        $sql = <<<SQL
            SELECT e.id,
                   e.title,
                   e.category,
                   e.description,
                   e.cover_image_id,
                   e.video_id,
                   e.user_id,
                   e.agent_id,
                   e.map_id,
                   e.created_at,
                   e.updated_at,
                   u.name  AS author_name,
                   a.name  AS agent_name,
                   a.photo AS agent_photo,
                   m.name  AS map_name,
                   CASE WHEN i.file_path IS NOT NULL
                        THEN :cover_prefix || i.file_path END AS cover_image_url,
                   CASE WHEN v.file_path IS NOT NULL
                        THEN :video_prefix || v.file_path END AS video_url,
                   v.duration AS video_duration,
                   COALESCE(AVG(r.rating), 0) AS rating_average,
                   COUNT(r.id)                AS ratings_count,
                   {$favoriteExpression}      AS is_favorite
              FROM strategies e
              LEFT JOIN users  u ON u.id = e.user_id
              LEFT JOIN agents a ON a.id = e.agent_id
              LEFT JOIN maps   m ON m.id = e.map_id
              LEFT JOIN images i ON i.id = e.cover_image_id
              LEFT JOIN videos v ON v.id = e.video_id
              LEFT JOIN ratings r ON r.strategy_id = e.id
              {$joins}
             WHERE {$where}
             GROUP BY e.id, u.name, a.name, a.photo, m.name, i.file_path, v.file_path, v.duration
             ORDER BY {$orderBy}
             LIMIT {$limit} OFFSET {$offset}
            SQL;

        /** @var list<self> $rows */
        $rows = Database::connection()->all($sql, $params, self::class);

        return $rows;
    }

    /**
     * Monta a cláusula WHERE, os parâmetros e os JOINs extras dos filtros.
     *
     * @param  array<string, mixed> $filters
     * @return array{0: string, 1: array<string, mixed>, 2: string}
     */
    private static function conditions(array $filters): array
    {
        $clauses = [];
        $params = [];
        $joins = '';

        if (isset($filters['id'])) {
            $clauses[] = 'e.id = :id';
            $params['id'] = (int) $filters['id'];
        }

        if (!empty($filters['user_id'])) {
            $clauses[] = 'e.user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            // A cláusula ESCAPE é obrigatória: sem declará-la, nem SQLite nem
            // PostgreSQL tratam a barra invertida como caractere de escape, e os
            // curingas escapados abaixo continuariam funcionando como curingas —
            // uma busca por "100%" traria a tabela inteira.
            $escape = " ESCAPE '\\'";

            $clauses[] = '('
                . "LOWER(e.title) LIKE LOWER(:search){$escape}"
                . " OR LOWER(e.category) LIKE LOWER(:search){$escape}"
                . " OR LOWER(a.name) LIKE LOWER(:search){$escape}"
                . " OR LOWER(m.name) LIKE LOWER(:search){$escape}"
                . ')';

            $params['search'] = '%' . addcslashes($search, '%_\\') . '%';
        }

        if (!empty($filters['agent_id'])) {
            $clauses[] = 'e.agent_id = :agent_id';
            $params['agent_id'] = (int) $filters['agent_id'];
        }

        if (!empty($filters['map_id'])) {
            $clauses[] = 'e.map_id = :map_id';
            $params['map_id'] = (int) $filters['map_id'];
        }

        if (!empty($filters['category'])) {
            $clauses[] = 'LOWER(e.category) = LOWER(:category)';
            $params['category'] = (string) $filters['category'];
        }

        if (!empty($filters['favorited_by'])) {
            $joins .= ' INNER JOIN favorites fv ON fv.strategy_id = e.id AND fv.user_id = :favorited_by';
            $params['favorited_by'] = (int) $filters['favorited_by'];
        }

        return [
            $clauses === [] ? '1 = 1' : implode(' AND ', $clauses),
            $params,
            $joins,
        ];
    }

    private static function resolveOrder(?string $order): string
    {
        return self::ORDERS[self::resolveOrderKey($order)];
    }

    private static function resolveOrderKey(?string $order): string
    {
        return is_string($order) && isset(self::ORDERS[$order])
            ? $order
            : self::DEFAULT_ORDER;
    }

    /**
     * Cria uma estratégia e devolve o id gerado.
     *
     * @param array<string, mixed> $attributes
     */
    public static function create(array $attributes): int
    {
        $database = Database::connection();

        $database->execute(
            'INSERT INTO strategies
                 (title, category, description, cover_image_id, video_id, user_id, agent_id, map_id)
             VALUES
                 (:title, :category, :description, :cover_image_id, :video_id, :user_id, :agent_id, :map_id)',
            [
                'title' => $attributes['title'],
                'category' => $attributes['category'],
                'description' => $attributes['description'],
                'cover_image_id' => $attributes['cover_image_id'] ?? null,
                'video_id' => $attributes['video_id'] ?? null,
                'user_id' => $attributes['user_id'],
                'agent_id' => $attributes['agent_id'],
                'map_id' => $attributes['map_id'],
            ],
        );

        return (int) $database->lastInsertId();
    }

    /**
     * Apaga a estratégia, garantindo no próprio WHERE que ela pertence ao autor.
     *
     * Checar a posse na consulta, e não só antes dela, fecha a janela em que a
     * estratégia poderia trocar de mãos entre a verificação e a escrita.
     */
    public static function deleteOwnedBy(int $id, int $userId): bool
    {
        return Database::connection()->execute(
            'DELETE FROM strategies WHERE id = :id AND user_id = :user_id',
            ['id' => $id, 'user_id' => $userId],
        ) > 0;
    }
}
