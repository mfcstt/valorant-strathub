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
}
