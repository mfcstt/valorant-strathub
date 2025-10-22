<?php

class Video {
    public $id;
    public $filename;
    public $original_name;
    public $file_path;
    public $file_size;
    public $mime_type;
    public $duration;
    public $user_id;
    public $created_at;
    
    private $database;

    public function __construct() {
        $this->database = new Database(config('database')['database']);
    }

    public function query($where = null, $params = []) {
        $sql = "SELECT * FROM videos";
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        
        $result = $this->database->query($sql, null, $params);
        
        if (is_array($result)) {
            return array_map(function($row) {
                $video = new self();
                foreach ($row as $key => $value) {
                    $video->$key = $value;
                }
                return $video;
            }, $result);
        }
        
        return $result;
    }

    public static function getByFilename($filename) {
        $result = (new self)->query('filename = :filename', ['filename' => $filename]);
        return is_array($result) ? ($result[0] ?? null) : $result;
    }

    public static function getUserVideos($user_id) {
        return (new self)->query('user_id = :user_id ORDER BY created_at DESC', ['user_id' => $user_id]);
    }

    public function save() {
        $result = $this->database->query(
            "INSERT INTO videos (filename, original_name, file_path, file_size, mime_type, duration, user_id) 
            VALUES (:filename, :original_name, :file_path, :file_size, :mime_type, :duration, :user_id)",
            null,
            [
                'filename' => $this->filename,
                'original_name' => $this->original_name,
                'file_path' => $this->file_path,
                'file_size' => $this->file_size,
                'mime_type' => $this->mime_type,
                'duration' => $this->duration,
                'user_id' => $this->user_id
            ]
        );
        
        if ($result) {
            // Obter o ID do vídeo recém-inserido
            $this->id = $this->database->lastInsertId();
        }
        
        return $result;
    }

    public function delete() {
        return $this->database->query(
            "DELETE FROM videos WHERE id = :id",
            null,
            ['id' => $this->id]
        );
    }

    public function getPublicUrl() {
        // Retorna a URL pública do vídeo no Supabase Storage
        $supabaseUrl = $_ENV['SUPABASE_URL'] ?? 'https://YOUR_PROJECT_REF.supabase.co';
        return $supabaseUrl . '/storage/v1/object/public/strategy-videos/' . $this->file_path;
    }

    /**
     * Formata a duração do vídeo em formato legível (mm:ss)
     */
    public function getFormattedDuration() {
        if (!$this->duration) {
            return '00:00';
        }
        
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Formata o tamanho do arquivo em formato legível
     */
    public function getFormattedFileSize() {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}