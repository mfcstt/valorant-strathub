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
 * apenas no log - quem chamava não tinha como dizer à pessoa o que aconteceu, e
 * o controller acabava mostrando "Erro ao fazer upload. Tente novamente." para
 * um arquivo grande demais e para uma chave de API inválida.
 *
 * Aqui as falhas previsíveis (validação) voltam como {@see StorageResult} com
 * mensagem própria, e as inesperadas (rede, credencial) sobem como exceção - a
 * distinção entre "a pessoa precisa corrigir algo" e "o sistema falhou".
 *
 * ## Dois caminhos de upload
 *
 * `uploadImage()`/`uploadVideo()` recebem o arquivo já no corpo da requisição
 * PHP ($_FILES) e o reenviam para o Supabase - o caminho original, que na
 * Vercel esbarra no limite de ~4,5 MB de toda função serverless (bem abaixo
 * dos 100 MB de vídeo que o formulário anuncia).
 *
 * `createSignedUpload()` + `finalizeUpload()` são o caminho usado quando o
 * navegador envia o arquivo direto para o Supabase, sem passar pela função:
 * a validação de tipo e tamanho, que antes lia os bytes recebidos, passa a
 * consultar o objeto já hospedado (HEAD na URL do Storage) - a mesma garantia
 * de "medir o conteúdo real, não confiar no que foi declarado", só que depois
 * do envio em vez de antes.
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

    public function supportsDirectUpload(): bool
    {
        return true;
    }

    /**
     * Envia uma imagem e registra os metadados no banco.
     *
     * @param array<string, mixed> $file entrada de $_FILES
     */
    public function uploadImage(array $file, int $userId): StorageResult
    {
        return $this->upload($file, $userId, 'image');
    }

    /**
     * @param array<string, mixed> $file
     */
    public function uploadVideo(array $file, int $userId): StorageResult
    {
        return $this->upload($file, $userId, 'video');
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
     * Pede ao Supabase uma URL de upload assinada - o navegador envia o
     * arquivo direto para ela, sem esse pedido carregar o binário.
     *
     * O objeto ainda não existe no bucket neste momento: só passa a existir
     * quando o navegador de fato faz o POST na URL devolvida.
     */
    public function createSignedUpload(string $kind, int $userId, string $extension): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException(
                'Supabase Storage não configurado: defina SUPABASE_URL e SUPABASE_SERVICE_KEY.'
            );
        }

        $bucket = $kind === 'image' ? $this->imageBucket : $this->videoBucket;

        // Mesmo esquema de nome do upload tradicional: gerado, nunca derivado
        // de nada que o cliente informe, para o caminho no bucket nunca ser
        // influenciável (inclusive contra "../").
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $objectPath = "user_{$userId}/{$filename}";
        $encodedPath = self::encodePath($objectPath);

        $response = $this->request(
            'POST',
            "{$this->baseUrl}/storage/v1/object/upload/sign/{$bucket}/{$encodedPath}",
            [],
            '{}',
        );

        if ($response['status'] < 200 || $response['status'] >= 300) {
            throw new RuntimeException(sprintf(
                'Supabase Storage recusou gerar a URL de upload (HTTP %d).',
                $response['status'],
            ));
        }

        /** @var array{url?: string}|null $decoded */
        $decoded = json_decode($response['body'], true);
        $relativeUrl = is_array($decoded) ? ($decoded['url'] ?? null) : null;

        if (!is_string($relativeUrl) || $relativeUrl === '') {
            throw new RuntimeException('Resposta inesperada do Supabase Storage ao assinar o upload.');
        }

        // A API devolve um caminho relativo (com o token já na query string);
        // a URL completa é o que o navegador de fato precisa para enviar.
        return [
            'upload_url' => "{$this->baseUrl}/storage/v1{$relativeUrl}",
            'path' => $objectPath,
        ];
    }

    /**
     * Confirma um upload feito direto pelo navegador: consulta o objeto já
     * hospedado (não confia em nada que o navegador tenha declarado antes de
     * enviar) e só então cria o registro em `images`/`videos`.
     */
    public function finalizeUpload(string $kind, string $path, int $userId): StorageResult
    {
        // O caminho é sempre "user_{id}/{32 hex}.{extensão}" - o formato exato
        // gerado por createSignedUpload(), nunca outra coisa. Checar só o
        // prefixo com str_starts_with() seria frágil contra um path como
        // "user_{id}/../user_{outro}/x.jpg": passa no prefixo, mas pode ser
        // resolvido pelo Storage como um objeto de outro usuário. Casar contra
        // o formato inteiro elimina esse desvio - e qualquer outro - de uma vez.
        if (!preg_match('#^user_' . $userId . '/[a-f0-9]{32}\.[a-z0-9]+$#', $path)) {
            return StorageResult::failure('Envio inválido.');
        }

        $bucket = $kind === 'image' ? $this->imageBucket : $this->videoBucket;
        $maxBytes = $kind === 'image' ? UploadValidator::MAX_IMAGE_BYTES : UploadValidator::MAX_VIDEO_BYTES;
        $allowedTypes = $kind === 'image' ? UploadValidator::imageTypes() : UploadValidator::videoTypes();
        $label = $kind === 'image' ? 'imagem' : 'vídeo';

        $head = $this->request('HEAD', $this->objectUrl($bucket, $path));

        if ($head['status'] < 200 || $head['status'] >= 300) {
            return StorageResult::failure(
                "Não encontramos o arquivo enviado. Tente selecionar a {$label} de novo.",
            );
        }

        $size = self::headerAsInt($head['headers'], 'content-length');
        $mime = self::headerValue($head['headers'], 'content-type');

        if ($size === null || $size <= 0) {
            $this->deleteObject($bucket, $path);

            return StorageResult::failure("O arquivo de {$label} está vazio.");
        }

        if ($size > $maxBytes) {
            $this->deleteObject($bucket, $path);

            return StorageResult::failure(sprintf(
                '%s muito grande. O limite é %d MB.',
                ucfirst($label),
                intdiv($maxBytes, 1024 * 1024),
            ));
        }

        // O Content-Type é o que o Supabase gravou no momento do envio - o
        // navegador o declara a partir do próprio arquivo (`File.type`), não
        // de um campo de formulário arbitrário, mas ainda assim é o cliente
        // quem envia: por isso o tamanho acima e a checagem de extensão do
        // nome (definida por nós em createSignedUpload(), nunca pelo cliente)
        // continuam sendo a defesa que não depende de nada declarado.
        if ($mime === null || !isset($allowedTypes[$mime])) {
            $this->deleteObject($bucket, $path);

            return StorageResult::failure(sprintf(
                'Formato de %s não suportado. Use: %s.',
                $label,
                implode(', ', array_unique(array_values($allowedTypes))),
            ));
        }

        $record = $this->buildRecord($kind, $path, $userId, $size, $mime);

        if (!$record->save()) {
            $this->deleteObject($bucket, $path);

            throw new RuntimeException('Falha ao registrar o arquivo no banco de dados.');
        }

        return StorageResult::success($record);
    }

    /**
     * @param array<string, mixed> $file
     * @param 'image'|'video'      $kind
     */
    private function upload(array $file, int $userId, string $kind): StorageResult
    {
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

        $bucket = $kind === 'image' ? $this->imageBucket : $this->videoBucket;

        $filename = bin2hex(random_bytes(16)) . '.' . $validation['extension'];
        $objectPath = "user_{$userId}/{$filename}";

        $contents = file_get_contents((string) $file['tmp_name']);
        if ($contents === false) {
            throw new RuntimeException('Não foi possível ler o arquivo enviado.');
        }

        $this->putObject($bucket, $objectPath, $contents, $validation['mime']);

        $record = $this->buildRecord($kind, $objectPath, $userId, (int) $file['size'], $validation['mime']);
        $record->original_name = self::sanitizeOriginalName((string) ($file['name'] ?? $filename));

        if (!$record->save()) {
            // O arquivo já subiu, mas o registro falhou: remover o objeto evita
            // deixar lixo órfão no bucket.
            $this->deleteObject($bucket, $objectPath);

            throw new RuntimeException('Falha ao registrar o arquivo no banco de dados.');
        }

        return StorageResult::success($record);
    }

    /**
     * Monta (sem salvar) o registro de Image ou Video para um objeto já
     * existente no bucket - usado pelos dois caminhos de upload.
     *
     * @param 'image'|'video' $kind
     */
    private function buildRecord(string $kind, string $path, int $userId, int $size, string $mime): MediaFile
    {
        $record = $kind === 'image' ? new Image() : new Video();

        $record->filename = basename($path);
        $record->original_name = basename($path);
        $record->file_path = $path;
        $record->file_size = $size;
        $record->mime_type = $mime;
        $record->user_id = $userId;

        if ($record instanceof Video) {
            $record->duration = null;
        }

        return $record;
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
        return "{$this->baseUrl}/storage/v1/object/{$bucket}/" . self::encodePath($path);
    }

    /**
     * Cada segmento é codificado separadamente para preservar as barras da
     * hierarquia do bucket.
     */
    private static function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    /**
     * @param  list<string> $headers
     * @return array{status: int, headers: array<string, string>, body: string}
     */
    private function request(string $method, string $url, array $headers = [], ?string $body = null): array
    {
        $handle = curl_init();

        curl_setopt_array($handle, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_NOBODY => $method === 'HEAD',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
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

        $raw = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($raw === false) {
            throw new RuntimeException("Falha de rede ao falar com o Supabase Storage: {$error}");
        }

        $rawResponse = (string) $raw;

        return [
            'status' => $status,
            'headers' => self::parseHeaders(substr($rawResponse, 0, $headerSize)),
            'body' => substr($rawResponse, $headerSize),
        ];
    }

    /**
     * @return array<string, string> nomes em minúsculo, último valor vence
     *         (redirects concatenam blocos de header; só a última resposta importa)
     */
    private static function parseHeaders(string $raw): array
    {
        $headers = [];

        foreach (explode("\r\n", $raw) as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }

            [$name, $value] = explode(':', $line, 2);
            $headers[strtolower(trim($name))] = trim($value);
        }

        return $headers;
    }

    /**
     * @param array<string, string> $headers
     */
    private static function headerValue(array $headers, string $name): ?string
    {
        $value = $headers[strtolower($name)] ?? null;

        // Content-Type pode vir com parâmetros ("video/mp4; charset=...");
        // só o tipo em si entra na comparação com a allowlist.
        return $value !== null ? trim(explode(';', $value)[0]) : null;
    }

    /**
     * @param array<string, string> $headers
     */
    private static function headerAsInt(array $headers, string $name): ?int
    {
        $value = $headers[strtolower($name)] ?? null;

        return $value !== null && ctype_digit($value) ? (int) $value : null;
    }

    /**
     * Guarda o nome original apenas como informação, já sem caminho e limitado
     * em tamanho - ele nunca é usado para montar caminho de arquivo.
     */
    private static function sanitizeOriginalName(string $name): string
    {
        return mb_substr(basename($name), 0, 255);
    }
}
