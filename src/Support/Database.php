<?php

declare(strict_types=1);

namespace App\Support;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Acesso ao banco via PDO, com uma única conexão por requisição.
 *
 * A versão anterior instanciava `new Database(...)` no construtor de cada model,
 * abrindo uma conexão TCP nova a cada objeto criado - dezenas por página, cada
 * uma pagando a latência de rede até o Supabase. Aqui a conexão é resolvida uma
 * vez e reaproveitada.
 */
final class Database
{
    private static ?self $instance = null;

    private PDO $pdo;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->pdo = $config['driver'] === 'sqlite'
            ? $this->connectSqlite($config)
            : $this->connectPostgres($config);
    }

    /**
     * Conexão compartilhada da requisição.
     */
    public static function connection(): self
    {
        if (self::$instance === null) {
            /** @var array<string, mixed> $config */
            $config = Config::get('database', []);
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Substitui a conexão compartilhada - usado pelos testes.
     */
    public static function swap(?self $database): void
    {
        self::$instance = $database;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Executa uma consulta e devolve o statement.
     *
     * @param array<string, mixed> $params
     */
    public function run(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    /**
     * Devolve todas as linhas, opcionalmente hidratadas numa classe.
     *
     * @param  array<string, mixed>  $params
     * @param  class-string|null     $class
     * @return array<int, mixed>
     */
    public function all(string $sql, array $params = [], ?string $class = null): array
    {
        $statement = $this->run($sql, $params);

        return $class !== null
            ? $statement->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $class)
            : $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devolve a primeira linha, ou null.
     *
     * @param  array<string, mixed>  $params
     * @param  class-string|null     $class
     */
    public function first(string $sql, array $params = [], ?string $class = null): mixed
    {
        $rows = $this->all($sql, $params, $class);

        return $rows[0] ?? null;
    }

    /**
     * Devolve a primeira coluna da primeira linha.
     *
     * @param array<string, mixed> $params
     */
    public function scalar(string $sql, array $params = []): mixed
    {
        return $this->run($sql, $params)->fetchColumn();
    }

    /**
     * Executa uma escrita e devolve o número de linhas afetadas.
     *
     * @param array<string, mixed> $params
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->run($sql, $params)->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Roda um callback dentro de uma transação, revertendo em caso de exceção.
     *
     * @template T
     * @param  callable(self): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback($this);
            $this->pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function connectSqlite(array $config): PDO
    {
        $path = (string) $config['database'];
        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0o775, true) && !is_dir($directory)) {
            throw new RuntimeException("Não foi possível criar o diretório do banco: {$directory}");
        }

        try {
            $pdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Falha ao conectar no SQLite: ' . $e->getMessage(), 0, $e);
        }

        $pdo->exec('PRAGMA foreign_keys = ON');
        $this->ensureSqliteSchema($pdo);

        return $pdo;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function connectPostgres(array $config): PDO
    {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['sslmode'] ?? 'require',
        );

        try {
            return new PDO($dsn, (string) $config['user'], (string) $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 10,
            ]);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'could not translate host name')) {
                throw new RuntimeException(
                    "Não foi possível resolver o host '{$config['host']}'. Verifique as "
                    . 'credenciais do Supabase ou use USE_SQLITE=true para rodar local.',
                    0,
                    $e,
                );
            }

            throw new RuntimeException('Falha ao conectar no banco de dados.', 0, $e);
        }
    }

    /**
     * Aplica o schema na primeira execução, para o SQLite funcionar sem setup.
     */
    private function ensureSqliteSchema(PDO $pdo): void
    {
        $exists = $pdo
            ->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")
            ?->fetchColumn();

        if ($exists !== false) {
            return;
        }

        $databaseDir = dirname(__DIR__, 2) . '/database';

        foreach (['schema.sqlite.sql', 'seeds.sql'] as $file) {
            $path = "{$databaseDir}/{$file}";
            $sql = is_readable($path) ? file_get_contents($path) : false;

            if ($sql === false) {
                throw new RuntimeException("Arquivo SQL não encontrado em {$path}");
            }

            $pdo->exec($sql);
        }
    }
}
