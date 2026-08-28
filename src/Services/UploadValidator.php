<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Validação de arquivos enviados por formulário.
 *
 * ## Por que não usar `$_FILES['x']['type']`
 *
 * Aquele campo é o header `Content-Type` que o navegador declarou - é entrada
 * do usuário, não uma medição. Um cliente qualquer pode enviar um executável
 * anunciando `image/png`. A versão anterior validava exatamente esse campo mais
 * a extensão do nome original, que também vem do cliente.
 *
 * Aqui o tipo é **medido** com `finfo`, que lê os bytes iniciais do arquivo, e a
 * extensão salva é **derivada** do tipo medido - o nome original nunca influencia
 * o caminho de destino.
 */
final class UploadValidator
{
    /**
     * Tipos aceitos para imagem, mapeados para a extensão canônica.
     *
     * @var array<string, string>
     */
    private const IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];

    /**
     * @var array<string, string>
     */
    private const VIDEO_TYPES = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogv',
        'video/quicktime' => 'mov',
    ];

    public const MAX_IMAGE_BYTES = 5 * 1024 * 1024;
    public const MAX_VIDEO_BYTES = 100 * 1024 * 1024;

    /**
     * Expostos para o upload direto (ver SupabaseStorageService::finalizeUpload()),
     * que precisa validar o tipo real de um arquivo que nunca passou por
     * validate() — o navegador o enviou direto para o Storage.
     *
     * @return array<string, string>
     */
    public static function imageTypes(): array
    {
        return self::IMAGE_TYPES;
    }

    /**
     * @return array<string, string>
     */
    public static function videoTypes(): array
    {
        return self::VIDEO_TYPES;
    }

    /**
     * Resultado da validação: erro legível ou o tipo e extensão detectados.
     *
     * @param  array<string, mixed> $file entrada de $_FILES
     * @return array{ok: true, mime: string, extension: string}|array{ok: false, error: string}
     */
    public static function validateImage(array $file): array
    {
        return self::validate($file, self::IMAGE_TYPES, self::MAX_IMAGE_BYTES, 'imagem');
    }

    /**
     * @param  array<string, mixed> $file
     * @return array{ok: true, mime: string, extension: string}|array{ok: false, error: string}
     */
    public static function validateVideo(array $file): array
    {
        return self::validate($file, self::VIDEO_TYPES, self::MAX_VIDEO_BYTES, 'vídeo');
    }

    /**
     * Um arquivo foi realmente enviado neste campo?
     *
     * @param array<string, mixed>|null $file
     */
    public static function wasUploaded(?array $file): bool
    {
        return is_array($file)
            && isset($file['error'])
            && (int) $file['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * @param array<string, mixed>|null $file
     */
    public static function isSuccessful(?array $file): bool
    {
        return is_array($file)
            && isset($file['error'])
            && (int) $file['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Mensagem legível para os códigos de erro de upload do PHP.
     */
    public static function describeError(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o tamanho máximo permitido.',
            UPLOAD_ERR_PARTIAL => 'O envio foi interrompido antes de terminar. Tente novamente.',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi selecionado.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar o arquivo no servidor.',
            UPLOAD_ERR_EXTENSION => 'O envio foi bloqueado por uma extensão do PHP.',
            default => 'Falha no envio do arquivo.',
        };
    }

    /**
     * @param  array<string, mixed>  $file
     * @param  array<string, string> $allowedTypes
     * @return array{ok: true, mime: string, extension: string}|array{ok: false, error: string}
     */
    private static function validate(array $file, array $allowedTypes, int $maxBytes, string $label): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => self::describeError($error)];
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');

        // Garante que o caminho recebido é de fato um upload desta requisição, e
        // não um caminho arbitrário do filesystem injetado no array.
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            return ['ok' => false, 'error' => "Envio de {$label} inválido."];
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0) {
            return ['ok' => false, 'error' => "O arquivo de {$label} está vazio."];
        }

        if ($size > $maxBytes) {
            return [
                'ok' => false,
                'error' => sprintf(
                    '%s muito grande. O limite é %d MB.',
                    ucfirst($label),
                    intdiv($maxBytes, 1024 * 1024),
                ),
            ];
        }

        $mime = self::detectMime($tmpName);
        if ($mime === null || !isset($allowedTypes[$mime])) {
            return [
                'ok' => false,
                'error' => sprintf(
                    'Formato de %s não suportado. Use: %s.',
                    $label,
                    implode(', ', array_unique(array_values($allowedTypes))),
                ),
            ];
        }

        return ['ok' => true, 'mime' => $mime, 'extension' => $allowedTypes[$mime]];
    }

    /**
     * Lê o tipo real do arquivo a partir do conteúdo.
     */
    private static function detectMime(string $path): ?string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return null;
        }

        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);

        return is_string($mime) && $mime !== '' ? $mime : null;
    }
}
