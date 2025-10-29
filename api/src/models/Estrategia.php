<?php

class Estrategia {
    public $id;
    public $title;
    public $category;
    public $description;
    public $cover_image_id;
    public $cover_image_url;
    public $video_id;
    public $video_url;
    public $video_duration;
    public $user_id;
    public $agent_id;
    public $agent_name;
    public $agent_photo;
    public $map_id;
    public $map_name;
    public $created_at;
    public $updated_at;
    public $rating_average;
    public $ratings_count;

    private $database;

    public function __construct() {
        $this->database = new Database(config('database')['database']);
    }

    public function query($where, $params = []) {
        $supabaseUrl = $_ENV['SUPABASE_URL'] ?? 'https://YOUR_PROJECT_REF.supabase.co';
        
        return $this->database->query(
            "SELECT 
                e.id, e.title, e.category, e.description, e.cover_image_id, e.video_id, e.user_id, e.agent_id, e.map_id, e.created_at, e.updated_at,
                a.name AS agent_name, a.photo AS agent_photo,
                m.name AS map_name,
                CASE 
                    WHEN i.file_path IS NOT NULL 
                    THEN CONCAT('$supabaseUrl/storage/v1/object/public/strategy-covers/', i.file_path)
                    ELSE NULL 
                END AS cover_image_url,
                CASE 
                    WHEN v.file_path IS NOT NULL 
                    THEN CONCAT('$supabaseUrl/storage/v1/object/public/strategy-videos/', v.file_path)
                    ELSE NULL 
                END AS video_url,
                v.duration AS video_duration,
                COALESCE(AVG(r.rating), 0) AS rating_average,
                COALESCE(COUNT(r.id), 0) AS ratings_count
            FROM estrategias e
            LEFT JOIN agents a ON a.id = e.agent_id
            LEFT JOIN maps m ON m.id = e.map_id
            LEFT JOIN images i ON i.id = e.cover_image_id
            LEFT JOIN videos v ON v.id = e.video_id
            LEFT JOIN ratings r ON r.estrategia_id = e.id
            WHERE $where
            GROUP BY e.id, a.name, a.photo, m.name, i.file_path, v.file_path, v.duration",
            self::class,
            $params
        );
    }

    public static function get($estrategia_id) {
        $result = (new self)->query('e.id = :estrategia_id', ['estrategia_id' => $estrategia_id]);
        return is_array($result) ? ($result[0] ?? null) : $result; // pega o primeiro objeto ou null
    }

    public static function all($search = '') {
        return (new self)->query(
            'LOWER(e.title) LIKE LOWER(:filter) OR LOWER(e.category) LIKE LOWER(:filter) OR LOWER(a.name) LIKE LOWER(:filter)',
            ['filter' => "%$search%"]
        ); // já retorna array de objetos
    }

    public static function myEstrategias($user_id) {
        return (new self)->query('e.user_id = :user_id', ['user_id' => $user_id]);
    }

    public static function filter($search = '', $agent_id = null, $map_id = null, $category = null, $user_id = null) {
        $clauses = [];
        $params = [];

        if ($user_id !== null) {
            $clauses[] = 'e.user_id = :user_id';
            $params['user_id'] = $user_id;
        }

        if ($search !== '') {
            $clauses[] = '(LOWER(e.title) LIKE LOWER(:filter) OR LOWER(e.category) LIKE LOWER(:filter) OR LOWER(a.name) LIKE LOWER(:filter) OR LOWER(m.name) LIKE LOWER(:filter))';
            $params['filter'] = "%$search%";
        }

        if (!empty($agent_id)) {
            $clauses[] = 'e.agent_id = :agent_id';
            $params['agent_id'] = $agent_id;
        }

        if (!empty($map_id)) {
            $clauses[] = 'e.map_id = :map_id';
            $params['map_id'] = $map_id;
        }

        if (!empty($category)) {
            $clauses[] = 'LOWER(e.category) = LOWER(:category)';
            $params['category'] = $category;
        }

        if (empty($clauses)) {
            $clauses[] = '1=1';
        }

        return (new self)->query(implode(' AND ', $clauses), $params);
    }

    /**
     * Versão paginada do filtro para evitar carregar tudo de uma vez
     */
    public static function filterPaginated($search = '', $agent_id = null, $map_id = null, $category = null, $user_id = null, $limit = 12, $offset = 0) {
        $clauses = [];
        $params = [];

        if ($user_id !== null) {
            $clauses[] = 'e.user_id = :user_id';
            $params['user_id'] = $user_id;
        }

        if ($search !== '') {
            $clauses[] = '(LOWER(e.title) LIKE LOWER(:filter) OR LOWER(e.category) LIKE LOWER(:filter) OR LOWER(a.name) LIKE LOWER(:filter) OR LOWER(m.name) LIKE LOWER(:filter))';
            $params['filter'] = "%$search%";
        }

        if (!empty($agent_id)) {
            $clauses[] = 'e.agent_id = :agent_id';
            $params['agent_id'] = $agent_id;
        }

        if (!empty($map_id)) {
            $clauses[] = 'e.map_id = :map_id';
            $params['map_id'] = $map_id;
        }

        if (!empty($category)) {
            $clauses[] = 'LOWER(e.category) = LOWER(:category)';
            $params['category'] = $category;
        }

        if (empty($clauses)) {
            $clauses[] = '1=1';
        }

        // LIMIT/OFFSET como inteiros
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);

        $supabaseUrl = $_ENV['SUPABASE_URL'] ?? 'https://YOUR_PROJECT_REF.supabase.co';
        $sql = "SELECT 
                e.id, e.title, e.category, e.description, e.cover_image_id, e.video_id, e.user_id, e.agent_id, e.map_id, e.created_at, e.updated_at,
                a.name AS agent_name, a.photo AS agent_photo,
                m.name AS map_name,
                CASE WHEN i.file_path IS NOT NULL THEN CONCAT('$supabaseUrl/storage/v1/object/public/strategy-covers/', i.file_path) ELSE NULL END AS cover_image_url,
                CASE WHEN v.file_path IS NOT NULL THEN CONCAT('$supabaseUrl/storage/v1/object/public/strategy-videos/', v.file_path) ELSE NULL END AS video_url,
                v.duration AS video_duration,
                COALESCE(AVG(r.rating), 0) AS rating_average,
                COALESCE(COUNT(r.id), 0) AS ratings_count
            FROM estrategias e
            LEFT JOIN agents a ON a.id = e.agent_id
            LEFT JOIN maps m ON m.id = e.map_id
            LEFT JOIN images i ON i.id = e.cover_image_id
            LEFT JOIN videos v ON v.id = e.video_id
            LEFT JOIN ratings r ON r.estrategia_id = e.id
            WHERE " . implode(' AND ', $clauses) . "
            GROUP BY e.id, a.name, a.photo, m.name, i.file_path, v.file_path, v.duration
            ORDER BY e.created_at DESC
            LIMIT $limit OFFSET $offset";

        // Buscar já mapeando para objetos da classe Estrategia
        return (new self)->database->query($sql, self::class, $params);
    }

    /**
     * Conta total de estratégias para os filtros aplicados
     */
    public static function countFiltered($search = '', $agent_id = null, $map_id = null, $category = null, $user_id = null) {
        $clauses = [];
        $params = [];

        if ($user_id !== null) {
            $clauses[] = 'e.user_id = :user_id';
            $params['user_id'] = $user_id;
        }

        if ($search !== '') {
            $clauses[] = '(LOWER(e.title) LIKE LOWER(:filter) OR LOWER(e.category) LIKE LOWER(:filter) OR LOWER(a.name) LIKE LOWER(:filter) OR LOWER(m.name) LIKE LOWER(:filter))';
            $params['filter'] = "%$search%";
        }

        if (!empty($agent_id)) {
            $clauses[] = 'e.agent_id = :agent_id';
            $params['agent_id'] = $agent_id;
        }

        if (!empty($map_id)) {
            $clauses[] = 'e.map_id = :map_id';
            $params['map_id'] = $map_id;
        }

        if (!empty($category)) {
            $clauses[] = 'LOWER(e.category) = LOWER(:category)';
            $params['category'] = $category;
        }

        if (empty($clauses)) { $clauses[] = '1=1'; }

        $sql = "SELECT COUNT(DISTINCT e.id) AS total
                FROM estrategias e
                LEFT JOIN agents a ON a.id = e.agent_id
                LEFT JOIN maps m ON m.id = e.map_id
                LEFT JOIN images i ON i.id = e.cover_image_id
                LEFT JOIN videos v ON v.id = e.video_id
                WHERE " . implode(' AND ', $clauses);

        $db = new self();
        $stmt = $db->database->query($sql, null, $params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }
}

?>
