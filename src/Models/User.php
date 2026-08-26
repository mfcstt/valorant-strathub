<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database;

final class User
{
    public mixed $id = null;
    public mixed $name = null;
    public mixed $email = null;
    public mixed $password = null;
    public mixed $avatar = null;
    public mixed $elo = null;
    public mixed $is_admin = null;
    public mixed $created_at = null;
    public mixed $updated_at = null;

    /**
     * Elos do Valorant, na ordem de progressão do jogo.
     */
    public const ELOS = [
        'ferro',
        'bronze',
        'prata',
        'ouro',
        'platina',
        'diamante',
        'ascendente',
        'imortal',
        'radiante',
    ];

    private const COLUMNS = 'id, name, email, password, avatar, elo, is_admin, created_at, updated_at';

    public static function find(int $id): ?self
    {
        $user = Database::connection()->first(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE id = :id',
            ['id' => $id],
            self::class,
        );

        return $user instanceof self ? $user : null;
    }

    public static function findByEmail(string $email): ?self
    {
        $user = Database::connection()->first(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE LOWER(email) = LOWER(:email)',
            ['email' => $email],
            self::class,
        );

        return $user instanceof self ? $user : null;
    }

    /**
     * Cria o usuário já com a senha hasheada e devolve o registro salvo.
     */
    public static function create(string $name, string $email, string $plainPassword, string $elo): ?self
    {
        $database = Database::connection();

        $database->execute(
            'INSERT INTO users (name, email, password, elo) VALUES (:name, :email, :password, :elo)',
            [
                'name' => $name,
                'email' => $email,
                'password' => self::hash($plainPassword),
                'elo' => self::normalizeElo($elo),
            ],
        );

        return self::find((int) $database->lastInsertId());
    }

    /**
     * @param array<string, mixed> $attributes aceita name, email, avatar e elo
     */
    public static function updateProfile(int $id, array $attributes): ?self
    {
        $allowed = ['name', 'email', 'avatar', 'elo'];
        $sets = [];
        $params = ['id' => $id];

        foreach ($allowed as $column) {
            if (array_key_exists($column, $attributes)) {
                $sets[] = "{$column} = :{$column}";
                $params[$column] = $column === 'elo'
                    ? self::normalizeElo((string) $attributes[$column])
                    : $attributes[$column];
            }
        }

        if ($sets === []) {
            return self::find($id);
        }

        Database::connection()->execute(
            'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id',
            $params,
        );

        return self::find($id);
    }

    public static function updatePassword(int $id, string $plainPassword): void
    {
        Database::connection()->execute(
            'UPDATE users SET password = :password WHERE id = :id',
            ['password' => self::hash($plainPassword), 'id' => $id],
        );
    }

    public static function delete(int $id): void
    {
        Database::connection()->execute('DELETE FROM users WHERE id = :id', ['id' => $id]);
    }

    /**
     * Quantas avaliações este usuário escreveu.
     */
    public static function ratingsCount(int $id): int
    {
        return (int) Database::connection()->scalar(
            'SELECT COUNT(*) FROM ratings WHERE user_id = :id',
            ['id' => $id],
        );
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, (string) $this->password);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public static function isValidElo(string $elo): bool
    {
        return in_array(strtolower(trim($elo)), self::ELOS, true);
    }

    public static function normalizeElo(string $elo): string
    {
        $elo = strtolower(trim($elo));

        return in_array($elo, self::ELOS, true) ? $elo : 'ferro';
    }

    /**
     * PASSWORD_DEFAULT deixa o PHP escolher o algoritmo mais forte disponível na
     * versão instalada, em vez de fixar bcrypt para sempre.
     */
    private static function hash(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }
}
