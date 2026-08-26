<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Config;

/**
 * Resolve qual implementação de armazenamento usar.
 *
 * Os controllers pedem `Storage::disk()` e não precisam saber se a mídia vai
 * para o Supabase ou para o disco local - a decisão é de configuração.
 */
final class Storage
{
    private static ?MediaStorage $disk = null;

    public static function disk(): MediaStorage
    {
        return self::$disk ??= match (Config::get('storage.driver')) {
            'supabase' => new SupabaseStorageService(),
            default => new LocalMediaStorage(),
        };
    }

    /**
     * Substitui a implementação - usado pelos testes.
     */
    public static function swap(?MediaStorage $disk): void
    {
        self::$disk = $disk;
    }
}
