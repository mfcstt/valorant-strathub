<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database;

/**
 * Avaliação de uma estratégia: uma nota de 1 a 5 mais um comentário.
 *
 * Cada usuário tem no máximo uma avaliação por estratégia (constraint UNIQUE);
 * enviar de novo atualiza a existente.
 */
final class Rating
{
    public mixed $id = null;
    public mixed $user_id = null;
    public mixed $user_name = null;
    public mixed $user_avatar = null;
    public mixed $user_elo = null;
    public mixed $strategy_id = null;
    public mixed $rating = null;
    public mixed $comment = null;
    public mixed $created_at = null;
    public mixed $updated_at = null;

    /** Quantas avaliações o autor deste comentário já escreveu no site. */
    public mixed $author_ratings_count = 0;

    public const MIN = 1;
    public const MAX = 5;

    /**
     * Avaliações de uma estratégia, das mais recentes para as mais antigas.
     *
     * @return array{items: list<self>, total: int, page: int, pages: int}
     */
    public static function paginateForStrategy(int $strategyId, int $page = 1, int $perPage = 5): array
    {
        $database = Database::connection();

        $total = (int) $database->scalar(
            'SELECT COUNT(*) FROM ratings WHERE strategy_id = :strategy_id',
            ['strategy_id' => $strategyId],
        );

        $pages = max(1, (int) ceil($total / max(1, $perPage)));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        /** @var list<self> $items */
        $items = $database->all(
            'SELECT r.id,
                    r.user_id,
                    r.strategy_id,
                    r.rating,
                    r.comment,
                    r.created_at,
                    r.updated_at,
                    u.name   AS user_name,
                    u.avatar AS user_avatar,
                    u.elo    AS user_elo,
                    (SELECT COUNT(*) FROM ratings ur WHERE ur.user_id = r.user_id)
                        AS author_ratings_count
               FROM ratings r
               LEFT JOIN users u ON u.id = r.user_id
              WHERE r.strategy_id = :strategy_id
              ORDER BY r.created_at DESC, r.id DESC
              LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset,
            ['strategy_id' => $strategyId],
            self::class,
        );

        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    /**
     * A avaliação que este usuário deu a esta estratégia, se houver.
     */
    public static function findByUserAndStrategy(int $userId, int $strategyId): ?self
    {
        $rating = Database::connection()->first(
            'SELECT id, user_id, strategy_id, rating, comment, created_at, updated_at
               FROM ratings
              WHERE user_id = :user_id AND strategy_id = :strategy_id',
            ['user_id' => $userId, 'strategy_id' => $strategyId],
            self::class,
        );

        return $rating instanceof self ? $rating : null;
    }

    /**
     * Cria ou atualiza a avaliação.
     *
     * O `ON CONFLICT ... DO UPDATE` original é sintaxe do PostgreSQL 9.5+ e
     * também funciona no SQLite 3.24+, mas a checagem explícita torna o caminho
     * legível e permite distinguir criação de atualização para a mensagem de
     * retorno - sem uma segunda consulta só para isso.
     *
     * @return bool true quando atualizou uma avaliação existente
     */
    public static function upsert(int $userId, int $strategyId, int $rating, string $comment): bool
    {
        $database = Database::connection();
        $existing = self::findByUserAndStrategy($userId, $strategyId);

        if ($existing !== null) {
            $database->execute(
                'UPDATE ratings
                    SET rating = :rating, comment = :comment
                  WHERE user_id = :user_id AND strategy_id = :strategy_id',
                [
                    'rating' => $rating,
                    'comment' => $comment,
                    'user_id' => $userId,
                    'strategy_id' => $strategyId,
                ],
            );

            return true;
        }

        $database->execute(
            'INSERT INTO ratings (user_id, strategy_id, rating, comment)
             VALUES (:user_id, :strategy_id, :rating, :comment)',
            [
                'user_id' => $userId,
                'strategy_id' => $strategyId,
                'rating' => $rating,
                'comment' => $comment,
            ],
        );

        return false;
    }

    public function value(): int
    {
        return (int) $this->rating;
    }

    public function avatarUrl(): string
    {
        $avatar = (string) ($this->user_avatar ?? '');

        return ($avatar === '' || $avatar === 'avatarDefault.png')
            ? '/assets/images/avatares/avatarDefault.png'
            : $avatar;
    }
}
