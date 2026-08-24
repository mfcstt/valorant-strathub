<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database;

/**
 * Vínculo entre um usuário e uma estratégia favoritada.
 *
 * A listagem de favoritas não vive aqui: ela é um filtro de
 * {@see Strategy::paginate()} (`favorited_by`), o que evita manter duas cópias
 * da mesma consulta gigante — era exatamente onde a versão anterior divergia,
 * com a lista de favoritas e a de explorar tendo ordenações diferentes.
 */
final class Favorite
{
    public mixed $id = null;
    public mixed $user_id = null;
    public mixed $strategy_id = null;
    public mixed $created_at = null;

    public static function exists(int $userId, int $strategyId): bool
    {
        return Database::connection()->scalar(
            'SELECT 1 FROM favorites WHERE user_id = :user_id AND strategy_id = :strategy_id',
            ['user_id' => $userId, 'strategy_id' => $strategyId],
        ) !== false;
    }

    /**
     * Alterna o favorito e devolve o estado resultante.
     *
     * @return bool true quando passou a ser favorita
     */
    public static function toggle(int $userId, int $strategyId): bool
    {
        $database = Database::connection();

        if (self::exists($userId, $strategyId)) {
            $database->execute(
                'DELETE FROM favorites WHERE user_id = :user_id AND strategy_id = :strategy_id',
                ['user_id' => $userId, 'strategy_id' => $strategyId],
            );

            return false;
        }

        $database->execute(
            'INSERT INTO favorites (user_id, strategy_id) VALUES (:user_id, :strategy_id)',
            ['user_id' => $userId, 'strategy_id' => $strategyId],
        );

        return true;
    }

    public static function countForUser(int $userId): int
    {
        return (int) Database::connection()->scalar(
            'SELECT COUNT(*) FROM favorites WHERE user_id = :user_id',
            ['user_id' => $userId],
        );
    }
}
