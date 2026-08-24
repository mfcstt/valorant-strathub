<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Image;
use App\Models\MediaFile;
use App\Models\Video;
use App\Support\Config;
use RuntimeException;

/**
 * Armazenamento em disco, dentro de `public/uploads`.
 *
 * Usado em desenvolvimento e como fallback quando não há credenciais do
 * Supabase. Não serve para produção em plataforma serverless, onde o
 * filesystem é efêmero — mas é exatamente o que permite clonar o repositório e
 * ver a aplicação funcionando por completo sem criar conta em serviço nenhum.
 */
final class LocalMediaStorage implements MediaStorage
{
    private string $basePath;
    private string $imageBucket;
    private string $videoBucket;

    public function __construct()
    {
        $this->basePath = (string) Config::get('storage.local_path');
        $this->imageBucket = (string) Config::get('storage.image_bucket');
        $this->videoBucket = (string) Config::get('storage.video_bucket');
    }

    /**
     * @param array<string, mixed> $file
     */
    public function uploadImage(array $file, int $userId): StorageResult
    {
        return $this->store($file, $userId, new Image(), $this->imageBucket, 'image');
    }

    /**
     * @param array<string, mixed> $file
     */
    public function uploadVideo(array $file, int $userId): StorageResult
    {
        return $this->store($file, $userId, new Video(), $this->videoBucket, 'video');
    }

    public function deleteImage(string $filePath): bool
    {
        return $this->remove($this->imageBucket, $filePath);
    }

    public function deleteVideo(string $filePath): bool
    {
        return $this->remove($this->videoBucket, $filePath);
    }

    /**
     * @param array<string, mixed> $file
     * @param 'image'|'video'      $kind
     */
    private function store(
        array $file,
        int $userId,
        MediaFile $record,
        string $bucket,
        string $kind,
    ): StorageResult {
        $validation = $kind === 'image'
            ? UploadValidator::validateImage($file)
            : UploadValidator::validateVideo($file);

        if ($validation['ok'] === false) {
            return StorageResult::failure($validation['error']);
        }

        // Nome gerado, jamais derivado do nome original: é o que impede o
        // cliente de influenciar o caminho de destino no disco.
        $filename = bin2hex(random_bytes(16)) . '.' . $validation['extension'];
        $relativePath = "user_{$userId}/{$filename}";
        $absolutePath = "{$this->basePath}/{$bucket}/{$relativePath}";

        $directory = dirname($absolutePath);
        if (!is_dir($directory) && !mkdir($directory, 0o775, true) && !is_dir($directory)) {
            throw new RuntimeException("Não foi possível criar o diretório {$directory}.");
        }

        // move_uploaded_file em vez de copy: só aceita arquivos que realmente
        // vieram do upload desta requisição.
        if (!move_uploaded_file((string) $file['tmp_name'], $absolutePath)) {
            throw new RuntimeException('Não foi possível gravar o arquivo enviado.');
        }

        $record->filename = $filename;
        $record->original_name = mb_substr(basename((string) ($file['name'] ?? $filename)), 0, 255);
        $record->file_path = $relativePath;
        $record->file_size = (int) $file['size'];
        $record->mime_type = $validation['mime'];
        $record->user_id = $userId;

        if ($record instanceof Video) {
            $record->duration = null;
        }

        if (!$record->save()) {
            @unlink($absolutePath);

            throw new RuntimeException('Falha ao registrar o arquivo no banco de dados.');
        }

        return StorageResult::success($record);
    }

    private function remove(string $bucket, string $filePath): bool
    {
        if ($filePath === '') {
            return false;
        }

        $target = realpath("{$this->basePath}/{$bucket}/{$filePath}");
        $root = realpath("{$this->basePath}/{$bucket}");

        // Confere que o caminho resolvido está dentro do bucket: um `file_path`
        // corrompido no banco não deve conseguir apagar arquivo fora dele.
        if ($target === false || $root === false || !str_starts_with($target, $root . DIRECTORY_SEPARATOR)) {
            return false;
        }

        return unlink($target);
    }
}
