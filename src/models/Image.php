<?php

class Image {
    public $id;
    public $filename;
    public $original_name;
    public $file_path;
    public $file_size;
    public $mime_type;
    public $user_id;
    public $created_at;

    private $database;

    public function __construct() {
        $this->database = new Database(config('database')['database']);
    }

    public function query($where, $params = []) {
        return $this->database->query(
            "SELECT * FROM images WHERE $where",
            self::class,
            $params
        );
    }

    public static function get($image_id) {
        $result = (new self)->query('id = :image_id', ['image_id' => $image_id]);
        return is_array($result) ? ($result[0] ?? null) : $result;
    }

    public static function getByFilename($filename) {
        $result = (new self)->query('filename = :filename', ['filename' => $filename]);
        return is_array($result) ? ($result[0] ?? null) : $result;
    }

    public static function getUserImages($user_id) {
        return (new self)->query('user_id = :user_id ORDER BY created_at DESC', ['user_id' => $user_id]);
    }

    public function save() {
        $result = $this->database->query(
            "INSERT INTO images (filename, original_name, file_path, file_size, mime_type, user_id) 
            VALUES (:filename, :original_name, :file_path, :file_size, :mime_type, :user_id)",
            null,
            [
                'filename' => $this->filename,
                'original_name' => $this->original_name,
                'file_path' => $this->file_path,
                'file_size' => $this->file_size,
                'mime_type' => $this->mime_type,
                'user_id' => $this->user_id
            ]
        );
        
        if ($result) {
            // Obter o ID da imagem recém-inserida
            $this->id = $this->database->lastInsertId();
        }
        
        return $result;
    }

    public function delete() {
        return $this->database->query(
            "DELETE FROM images WHERE id = :id",
            null,
            ['id' => $this->id]
        );
    }

    public function getPublicUrl() {
        // Retorna a URL pública da imagem no Supabase Storage
        $supabaseUrl = $_ENV['SUPABASE_URL'] ?? 'https://YOUR_PROJECT_REF.supabase.co';
        return $supabaseUrl . '/storage/v1/object/public/strategy-covers/' . $this->file_path;
    }
}
