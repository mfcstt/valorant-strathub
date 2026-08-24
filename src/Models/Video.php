<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Vídeo demonstrativo de uma estratégia.
 */
final class Video extends MediaFile
{
    public mixed $duration = null;

    protected static function table(): string
    {
        return 'videos';
    }

    protected static function publicPrefixKey(): string
    {
        return 'storage.video_prefix';
    }

    /**
     * @return list<string>
     */
    protected static function columns(): array
    {
        return [...parent::columns(), 'duration'];
    }

    /**
     * Duração no formato mm:ss.
     */
    public function formattedDuration(): string
    {
        $seconds = (int) ($this->duration ?? 0);

        if ($seconds <= 0) {
            return '--:--';
        }

        return sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60);
    }
}
