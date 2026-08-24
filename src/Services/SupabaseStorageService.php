<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Image;
use App\Models\MediaFile;
use App\Models\Video;
use App\Support\Config;
use RuntimeException;

/**
 * Envio e remoção de arquivos no Supabase Storage, via API HTTP.
 *
 * ## Tratamento de erro
 *
 * A versão anterior devolvia `false` para qualquer falha e registrava a causa
 * apenas no log — quem chamava não tinha como dizer à pessoa o que aconteceu, e
 * o controller acabava mostrando "Erro ao fazer upload. Tente novamente." para
 * um arquivo grande demais e para uma chave de API inválida.
 *
 * Aqui as falhas previsíveis (validação) voltam como {@see StorageResult} com
 * mensagem própria, e as inesperadas (rede, credencial) sobem como exceção — a
 * distinção entre "a pessoa precisa corrigir algo" e "o sistema falhou".
 */
final class SupabaseStorageService implements MediaStorage
{
    private string $baseUrl;
    private string $apiKey;
    private string $imageBucket;
    private string $videoBucket;

    public function __construct()
    {
        $this->baseUrl = (string) Config::get('storage.url', '');
        $this->apiKey = (string) Config::get('storage.service_key', '');
        $this->imageBucket = (string) Config::get('storage.image_bucket', 'strategy-covers');
        $this->videoBucket = (string) Config::get('storage.video_bucket', 'strategy-videos');
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }

    /**
     * Envia uma imagem e registra os metadados no banco.
     *
     * @param array<string, mixed> $file entrada de $_FILES
     */
    public function uploadImage(array $file, int $userId): StorageResult
    {
        return $this->upload($file, $userId, new Image(), $this->imageBucket, 'image');
    }

    /**
     * @param array<string, mixed> $file
     */
    public function uploadVideo(array $file, int $userId): StorageResult
    {
        return $this->upload($file, $userId, new Video(), $this->videoBucket, 'video');
    }

    public function deleteImage(string $filePath): bool
    {
        return $this->deleteObject($this->imageBucket, $filePath);
    }

    public function deleteVideo(string $filePath): bool
    {
        return $this->deleteObject($this->videoBucket, $filePath);
    }

    /**
     * @param array<string, mixed> $file
     * @param 'image'|'video'      $kind
     */
    private function upload(
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

        if (!$this->isConfigured()) {
            throw new RuntimeException(
                'Supabase Storage não configurado: defina SUPABASE_URL e SUPABASE_SERVICE_KEY.'
            );
        }

        // O nome de destino é gerado, nunca derivado do nome original: isso evita
        // que o cliente influencie o caminho no bucket (inclusive com "../").
        $filename = bin2hex(random_bytes(16)) . '.' . $validation['extension'];
        $objectPath = "user_{$userId}/{$filename}";

        $contents = file_get_contents((string) $file['tmp_name']);
        if ($contents === false) {
            throw new RuntimeException('Não foi possível ler o arquivo enviado.');
        }

        $this->putObject($bucket, $objectPath, $contents, $validation['mime']);

        $record->filename = $filename;
        $record->original_name = self::sanitizeOriginalName((string) ($file['name'] ?? $filename));
        $record->file_path = $objectPath;
        $record->file_size = (int) $file['size'];
        $record->mime_type = $validation['mime'];
        $record->user_id = $userId;

        if ($record instanceof Video) {
            $record->duration = null;
        }

        if (!$record->save()) {
            // O arquivo já subiu, mas o registro falhou: remover o objeto evita
            // deixar lixo órfão no bucket.
            $this->deleteObject($bucket, $objectPath);

            throw new RuntimeException('Falha ao registrar o arquivo no banco de dados.');
        }

        return StorageResult::success($record);
    }

    private function putObject(string $bucket, string $path, string $contents, string $mime): void
    {
        $response = $this->request('POST', $this->objectUrl($bucket, $path), [
            'Content-Type: ' . $mime,
            'x-upsert: true',
        ], $contents);

        if ($response['status'] < 200 || $response['status'] >= 300) {
            throw new RuntimeException(sprintf(
                'Supabase Storage recusou o envio (HTTP %d).',
                $response['status'],
            ));
        }
    }

    private function deleteObject(string $bucket, string $path): bool
    {
        if (!$this->isConfigured() || $path === '') {
            return false;
        }

        $response = $this->request('DELETE', $this->objectUrl($bucket, $path));

        return $response['status'] >= 200 && $response['status'] < 300;
    }

    private function objectUrl(string $bucket, string $path): string
    {
        // Cada segmento é codificado separadamente para preservar as barras da
        // hierarquia do bucket.
        $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));

        return "{$this->baseUrl}/storage/v1/object/{$bucket}/{$encoded}";
    }

    /**
     * @param  list<string> $headers
     * @return array{status: int, body: string}
     */
    private function request(string $method, string $url, array $headers = [], ?string $body = null): array
    {
        $handle = curl_init();

        curl_setopt_array($handle, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'apikey: ' . $this->apiKey,
                ...$headers,
            ],
        ]);

        if ($body !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($response === false) {
            throw new RuntimeException("Falha de rede ao falar com o Supabase Storage: {$error}");
        }

        return ['status' => $status, 'body' => (string) $response];
    }

    /**
     * Guarda o nome original apenas como informação, já sem caminho e limitado
     * em tamanho — ele nunca é usado para montar caminho de arquivo.
     */
    private static function sanitizeOriginalName(string $name): string
    {
        return mb_substr(basename($name), 0, 255);
    }
}
