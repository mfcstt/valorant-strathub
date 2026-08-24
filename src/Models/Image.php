<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Imagem de capa de uma estratégia (ou avatar de usuário).
 */
final class Image extends MediaFile
{
    protected static function table(): string
    {
        return 'images';
    }

    protected static function publicPrefixKey(): string
    {
        return 'storage.image_prefix';
    }
}
