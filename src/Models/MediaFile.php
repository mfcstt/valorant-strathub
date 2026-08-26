<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Config;
use App\Support\Database;

/**
 * Metadados de um arquivo guardado no Supabase Storage.
 *
 * `Image` e `Video` eram duas classes praticamente idênticas - mesmas colunas,
 * mesmo `save()`, mesmo `delete()`, mesmo cálculo de URL pública, com o bucket
 * como única diferença real. O comportamento comum vive aqui; as subclasses
 * declaram apenas o que muda.
 */
abstract class MediaFile
{
    public mixed $id = null;
    public mixed $filename = null;
    public mixed $original_name = null;
    public mixed $file_path = null;
    public mixed $file_size = null;
    public mixed $mime_type = null;
    public mixed $user_id = null;
    public mixed $created_at = null;

    /**
     * Nome da tabela. Valor fixo por subclasse - nunca vem de entrada externa.
     */
    abstract protected static function table(): string;

    /**
     * @return list<string> colunas persistidas, na ordem do INSERT
     */
    protected static function columns(): array
    {
        return ['filename', 'original_name', 'file_path', 'file_size', 'mime_type', 'user_id'];
    }

    public static function find(int $id): ?static
    {
        $record = Database::connection()->first(
            'SELECT * FROM ' . static::table() . ' WHERE id = :id',
            ['id' => $id],
            static::class,
        );

        return $record instanceof static ? $record : null;
    }

    /**
     * @return list<static>
     */
    public static function forUser(int $userId): array
    {
        /** @var list<static> $records */
        $records = Database::connection()->all(
            'SELECT * FROM ' . static::table() . ' WHERE user_id = :user_id ORDER BY created_at DESC',
            ['user_id' => $userId],
            static::class,
        );

        return $records;
    }

    /**
     * Persiste o registro e preenche `$this->id`.
     */
    public function save(): bool
    {
        $columns = static::columns();
        $placeholders = array_map(static fn (string $c): string => ':' . $c, $columns);

        $params = [];
        foreach ($columns as $column) {
            $params[$column] = $this->{$column};
        }

        $database = Database::connection();
        $database->execute(
            sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                static::table(),
                implode(', ', $columns),
                implode(', ', $placeholders),
            ),
            $params,
        );

        $this->id = (int) $database->lastInsertId();

        return $this->id > 0;
    }

    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }

        return Database::connection()->execute(
            'DELETE FROM ' . static::table() . ' WHERE id = :id',
            ['id' => $this->id],
        ) > 0;
    }

    /**
     * URL pública do arquivo.
     *
     * O prefixo vem de `config('storage')` e já leva em conta o driver ativo
     * (Supabase ou disco local), então esta classe não repete a fórmula.
     */
    public function publicUrl(): string
    {
        return Config::get(static::publicPrefixKey()) . $this->file_path;
    }

    /**
     * Chave em `config('storage')` com o prefixo público da URL.
     */
    abstract protected static function publicPrefixKey(): string;

    /**
     * Tamanho legível por pessoas: "2,4 MB".
     */
    public function humanFileSize(): string
    {
        $bytes = (float) $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;

        while ($bytes >= 1024 && $unit < count($units) - 1) {
            $bytes /= 1024;
            $unit++;
        }

        return number_format($bytes, $unit === 0 ? 0 : 1, ',', '.') . ' ' . $units[$unit];
    }
}
