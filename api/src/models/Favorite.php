<?php

class Favorite {
    public $id;
    public $user_id;
    public $estrategia_id;
    public $created_at;

    private $database;

    public function __construct() {
        $this->database = new Database(config('database')['database']);
    }

    public function query($where = '1=1', $params = []) {
        return $this->database->query(
            "SELECT id, user_id, estrategia_id, created_at FROM favorites WHERE $where",
            self::class,
            $params
        );
    }

    public static function isFavorite($user_id, $estrategia_id) {
        $db = new self();
        $stmt = $db->database->query(
            "SELECT 1 FROM favorites WHERE user_id = :user_id AND estrategia_id = :estrategia_id",
            null,
            compact('user_id', 'estrategia_id')
        );
        return (bool)($stmt && $stmt->fetchColumn());
    }

    public static function toggle($user_id, $estrategia_id) {
        $db = new self();
        if (self::isFavorite($user_id, $estrategia_id)) {
            $db->database->query(
                "DELETE FROM favorites WHERE user_id = :user_id AND estrategia_id = :estrategia_id",
                null,
                compact('user_id', 'estrategia_id')
            );
            return false; // agora não é favorito
        } else {
            $db->database->query(
                "INSERT INTO favorites (user_id, estrategia_id) VALUES (:user_id, :estrategia_id)",
                null,
                compact('user_id', 'estrategia_id')
            );
            return true; // agora é favorito
        }
    }

    public static function listPaginated($user_id, $limit = 10, $offset = 0, $search = '', $agent_id = null, $map_id = null, $category = null) {
        // Retorna estratégias favoritas do usuário mapeadas como objetos Estrategia com suporte a filtros
        $db = new self();
        $supabaseUrl = $_ENV['SUPABASE_URL'] ?? 'https://YOUR_PROJECT_REF.supabase.co';

        $clauses = ['f.user_id = :user_id'];
        $params = ['user_id' => $user_id];

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

        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);

        $sql = "SELECT 
                    e.id, e.title, e.category, e.description, e.cover_image_id, e.video_id, e.user_id, e.agent_id, e.map_id, e.created_at, e.updated_at,
                    a.name AS agent_name, a.photo AS agent_photo,
                    m.name AS map_name,
                    CASE WHEN i.file_path IS NOT NULL THEN CONCAT('$supabaseUrl/storage/v1/object/public/strategy-covers/', i.file_path) ELSE NULL END AS cover_image_url,
                    CASE WHEN v.file_path IS NOT NULL THEN CONCAT('$supabaseUrl/storage/v1/object/public/strategy-videos/', v.file_path) ELSE NULL END AS video_url,
                    v.duration AS video_duration,
                    COALESCE(AVG(r.rating), 0) AS rating_average,
                    COALESCE(COUNT(r.id), 0) AS ratings_count
                FROM favorites f
                INNER JOIN estrategias e ON e.id = f.estrategia_id
                LEFT JOIN agents a ON a.id = e.agent_id
                LEFT JOIN maps m ON m.id = e.map_id
                LEFT JOIN images i ON i.id = e.cover_image_id
                LEFT JOIN videos v ON v.id = e.video_id
                LEFT JOIN ratings r ON r.estrategia_id = e.id
                WHERE " . implode(' AND ', $clauses) . "
                GROUP BY e.id, a.name, a.photo, m.name, i.file_path, v.file_path, v.duration
                ORDER BY MAX(f.created_at) DESC
                LIMIT $limit OFFSET $offset";

        return $db->database->query($sql, Estrategia::class, $params);
    }

    public static function countByUser($user_id, $search = '', $agent_id = null, $map_id = null, $category = null) {
        $db = new self();
        $clauses = ['f.user_id = :user_id'];
        $params = ['user_id' => $user_id];

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

        $sql = "SELECT COUNT(DISTINCT e.id) AS total
                FROM favorites f
                INNER JOIN estrategias e ON e.id = f.estrategia_id
                LEFT JOIN agents a ON a.id = e.agent_id
                LEFT JOIN maps m ON m.id = e.map_id
                WHERE " . implode(' AND ', $clauses);

        $stmt = $db->database->query($sql, null, $params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }
}

?>