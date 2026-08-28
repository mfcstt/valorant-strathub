<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Contrato de armazenamento de mídia.
 *
 * Duas implementações: {@see SupabaseStorageService} para produção e
 * {@see LocalMediaStorage} para desenvolvimento. A interface existe para o
 * projeto rodar sem nenhuma conta externa - antes, o único caminho possível
 * exigia credenciais do Supabase para criar qualquer estratégia com imagem.
 *
 * ## Upload direto (createSignedUpload / finalizeUpload)
 *
 * A Vercel corta toda requisição de função serverless em ~4,5 MB - bem abaixo
 * dos 50 MB de vídeo e 5 MB de imagem que o formulário anuncia. Um vídeo
 * passado direto no corpo do POST para uploadVideo() falha com HTTP 413 antes
 * mesmo de o PHP rodar, então o navegador precisa enviar o arquivo grande
 * direto para o Storage - a função serverless só participa dos dois pedaços
 * pequenos: pedir a URL assinada e, depois, confirmar o que chegou lá.
 *
 * Só o Supabase suporta esse caminho (LocalMediaStorage devolve null em
 * createSignedUpload() e o formulário cai de volta no upload tradicional).
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

    /**
     * Se este driver permite o navegador enviar o arquivo direto para o
     * Storage, sem passar pela função serverless.
     */
    public function supportsDirectUpload(): bool;

    /**
     * Gera uma URL de upload assinada para o navegador enviar o arquivo
     * direto, sem esse pedido nunca carregar o binário em si.
     *
     * @param  'image'|'video'                        $kind
     * @return array{upload_url: string, path: string}|null null quando o
     *         driver não suporta (ver {@see supportsDirectUpload()})
     */
    public function createSignedUpload(string $kind, int $userId, string $extension): ?array;

    /**
     * Confirma um upload feito direto pelo navegador via createSignedUpload():
     * valida o arquivo já hospedado (tipo e tamanho reais, medidos no Storage
     * - nunca o que o navegador declarou antes de enviar) e cria o registro.
     *
     * @param 'image'|'video' $kind
     */
    public function finalizeUpload(string $kind, string $path, int $userId): StorageResult;
}
