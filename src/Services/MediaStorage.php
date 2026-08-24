<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Contrato de armazenamento de mídia.
 *
 * Duas implementações: {@see SupabaseStorageService} para produção e
 * {@see LocalMediaStorage} para desenvolvimento. A interface existe para o
 * projeto rodar sem nenhuma conta externa — antes, o único caminho possível
 * exigia credenciais do Supabase para criar qualquer estratégia com imagem.
 */
interface MediaStorage
{
    /**
     * @param array<string, mixed> $file entrada de $_FILES
     */
    public function uploadImage(array $file, int $userId): StorageResult;

    /**
     * @param array<string, mixed> $file entrada de $_FILES
     */
    public function uploadVideo(array $file, int $userId): StorageResult;

    public function deleteImage(string $filePath): bool;

    public function deleteVideo(string $filePath): bool;
}
