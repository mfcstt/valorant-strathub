<?php

class Estrategia {
    public $id;
    public $title;
    public $category;
    public $description;
    public $cover;
    public $user_id;
    public $agent_id;
    public $agent_name;
    public $agent_photo;
    public $created_at;
    public $updated_at;
    public $rating_average;
    public $ratings_count;

    private $database;

    public function __construct() {
        $this->database = new Database(config('database')['database']);
    }

    public function query($where, $params = []) {
        return $this->database->query(
            "SELECT 
                e.id, e.title, e.category, e.description, e.cover, e.user_id, e.agent_id, e.created_at, e.updated_at,
                a.name AS agent_name, a.photo AS agent_photo,
                COALESCE(SUM(r.rating)/COUNT(r.id), 0) AS rating_average,
                COALESCE(COUNT(r.id), 0) AS ratings_count
            FROM estrategias e
            LEFT JOIN agents a ON a.id = e.agent_id
            LEFT JOIN ratings r ON r.estrategia_id = e.id
            WHERE $where
            GROUP BY e.id, a.name, a.photo",
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
            'e.title ILIKE :filter OR e.category ILIKE :filter OR a.name ILIKE :filter',
            ['filter' => "%$search%"]
        ); // já retorna array de objetos
    }

    public static function myEstrategias($user_id) {
        return (new self)->query('e.user_id = :user_id', ['user_id' => $user_id]);
    }
}
