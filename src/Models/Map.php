<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database;

/**
 * Mapa do Valorant — dado de referência, populado por database/seeds.sql.
 */
final class Map
{
    public mixed $id = null;
    public mixed $name = null;
    public mixed $image = null;
    public mixed $created_at = null;

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        /** @var list<self> $maps */
        $maps = Database::connection()->all(
            'SELECT id, name, image, created_at FROM maps ORDER BY name ASC',
            [],
            self::class,
        );

        return $maps;
    }

    public static function find(int $id): ?self
    {
        $map = Database::connection()->first(
            'SELECT id, name, image, created_at FROM maps WHERE id = :id',
            ['id' => $id],
            self::class,
        );

        return $map instanceof self ? $map : null;
    }

    public function imageUrl(): string
    {
        return '/assets/images/maps/' . ($this->image ?? 'default.png');
    }
}
