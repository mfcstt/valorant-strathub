<?php

class User {
    public $id;
    public $name;
    public $email;
    public $password;
    public $avatar;
    public $created_at;
    public $updated_at;

    private $database;

    public function __construct() {
        $this->database = new Database(config('database')['database']);
    }

    // Consulta genérica
    public function query($where = '1=1', $params = []) {
        return $this->database->query(
            "SELECT id, name, email, password, avatar, created_at, updated_at
             FROM users
             WHERE $where",
            self::class,
            $params
        );
    }

    // Pegar um usuário pelo ID
    public static function get($user_id) {
        $result = (new self)->query('id = :id', ['id' => $user_id]);
        return $result[0] ?? null;
    }

    // Pegar um usuário pelo email
    public static function getByEmail($email) {
        $result = (new self)->query('email = :email', ['email' => $email]);
        return $result[0] ?? null;
    }

    // Pegar todos os usuários
    public static function all() {
        return (new self)->query();
    }

    // Criar novo usuário
    public function create($data) {
        $stmt = $this->database->prepare(
            "INSERT INTO users (name, email, password, avatar) 
             VALUES (:name, :email, :password, :avatar)
             RETURNING *"
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => $data['password'], // já hash, se necessário
            ':avatar' => $data['avatar'] ?? 'avatarDefault.png'
        ]);

        $result = $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
        return $result[0] ?? null;
    }

    // Atualizar usuário
    public function update($id, $data) {
        $stmt = $this->database->prepare(
            "UPDATE users
             SET name = :name, email = :email, avatar = :avatar
             WHERE id = :id
             RETURNING *"
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':avatar' => $data['avatar'] ?? 'avatarDefault.png',
            ':id' => $id
        ]);

        $result = $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
        return $result[0] ?? null;
    }
}
