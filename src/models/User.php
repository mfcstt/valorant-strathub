<?php

class User {
    public $id;
    public $name;
    public $email;
    public $password;
    public $avatar;
    public $elo;
    public $created_at;
    public $updated_at;

    private $database;

    public function __construct() {
        $this->database = new Database(config('database')['database']);
    }

    // Consulta genérica
    public function query($where = '1=1', $params = []) {
        return $this->database->query(
            "SELECT id, name, email, password, avatar, elo, created_at, updated_at
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
            "INSERT INTO users (name, email, password, avatar, elo) 
             VALUES (:name, :email, :password, :avatar, :elo)
             RETURNING *"
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => $data['password'], // já hash, se necessário
            ':avatar' => $data['avatar'] ?? 'avatarDefault.png',
            ':elo' => $data['elo'] ?? 'ferro',
        ]);

        $result = $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
        return $result[0] ?? null;
    }

    // Atualizar usuário (nome/email/avatar)
    public function update($id, $data) {
        // Executa o UPDATE usando a API de query do Database
        $this->database->query(
            "UPDATE users
             SET name = :name, email = :email, avatar = :avatar
             WHERE id = :id",
            null,
            [
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':avatar' => $data['avatar'] ?? 'avatarDefault.png',
                ':id' => $id
            ]
        );

        // Busca o usuário atualizado
        return $this->database->fetchOne(
            "SELECT id, name, email, password, avatar, elo, created_at, updated_at
             FROM users WHERE id = :id",
            self::class,
            [':id' => $id]
        );
    }

    public function updatePassword($id, $plainPassword) {
        $hashed = password_hash($plainPassword, PASSWORD_BCRYPT);
        $this->database->query(
            "UPDATE users SET password = :password WHERE id = :id",
            null,
            [
                ':password' => $hashed,
                ':id' => $id
            ]
        );
    
        return $this->database->fetchOne(
            "SELECT id, name, email, password, avatar, elo, created_at, updated_at
             FROM users WHERE id = :id",
            self::class,
            [':id' => $id]
        );
    }

    public function updateElo($id, $elo) {
        $this->database->query(
            "UPDATE users SET elo = :elo WHERE id = :id",
            null,
            [
                ':elo' => $elo,
                ':id' => $id,
            ]
        );

        return $this->database->fetchOne(
            "SELECT id, name, email, password, avatar, elo, created_at, updated_at
             FROM users WHERE id = :id",
            self::class,
            [':id' => $id]
        );
    }

    public function delete($id) {
        $this->database->query(
            "DELETE FROM users WHERE id = :id",
            null,
            [':id' => $id]
        );
    }
}
