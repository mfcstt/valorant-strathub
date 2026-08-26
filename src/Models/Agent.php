<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database;

/**
 * Agente do Valorant - dado de referência, populado por database/seeds.sql.
 */
final class Agent
{
    public mixed $id = null;
    public mixed $name = null;
    public mixed $photo = null;
    public mixed $created_at = null;

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        /** @var list<self> $agents */
        $agents = Database::connection()->all(
            'SELECT id, name, photo, created_at FROM agents ORDER BY name ASC',
            [],
            self::class,
        );

        return $agents;
    }

    public static function find(int $id): ?self
    {
        $agent = Database::connection()->first(
            'SELECT id, name, photo, created_at FROM agents WHERE id = :id',
            ['id' => $id],
            self::class,
        );

        return $agent instanceof self ? $agent : null;
    }

    public function photoUrl(): string
    {
        return '/assets/images/agents/' . ($this->photo ?? 'default.png');
    }
}
