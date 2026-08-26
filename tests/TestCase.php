<?php

declare(strict_types=1);

namespace Tests;

use App\Support\Database;
use PDO;
use PHPUnit\Framework\TestCase as BaseTestCase;
use RuntimeException;

/**
 * Base dos testes: banco isolado e estado global limpo.
 *
 * ## Por que a suíte roda em dois bancos
 *
 * A suíte usava só SQLite, e isso deixou passar um bug que só existia no
 * PostgreSQL: o PDO reescreve parâmetros nomeados para `$1`, `$2`… no driver
 * pgsql, e uma cláusula `ESCAPE '\'` confundia esse parser, quebrando a busca em
 * produção enquanto todos os testes passavam.
 *
 * Definindo `TEST_DB_DRIVER=pgsql`, os mesmos testes rodam contra um Postgres
 * real - é o que a CI faz num job separado.
 */
abstract class TestCase extends BaseTestCase
{
    private ?string $sqlitePath = null;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        $_COOKIE = [];
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
    }

    protected function tearDown(): void
    {
        Database::swap(null);

        if ($this->sqlitePath !== null && is_file($this->sqlitePath)) {
            unlink($this->sqlitePath);
        }
        $this->sqlitePath = null;

        parent::tearDown();
    }

    /**
     * Devolve uma conexão limpa, com schema e seeds aplicados.
     */
    protected function useDatabase(): Database
    {
        $database = self::driver() === 'pgsql'
            ? $this->makePostgres()
            : $this->makeSqlite();

        Database::swap($database);

        return $database;
    }

    protected static function driver(): string
    {
        return getenv('TEST_DB_DRIVER') ?: 'sqlite';
    }

    /**
     * Arquivo temporário por teste: isolamento total, sem custo de setup.
     */
    private function makeSqlite(): Database
    {
        $this->sqlitePath = tempnam(sys_get_temp_dir(), 'strathub-test-') . '.sqlite';

        return new Database([
            'driver' => 'sqlite',
            'database' => $this->sqlitePath,
        ]);
    }

    /**
     * Banco Postgres compartilhado, recriado a cada teste.
     *
     * Um banco por teste seria lento demais; em vez disso o schema `public` é
     * derrubado e recriado, o que dá o mesmo isolamento.
     */
    private function makePostgres(): Database
    {
        $config = [
            'driver' => 'pgsql',
            'host' => getenv('TEST_DB_HOST') ?: '127.0.0.1',
            'port' => getenv('TEST_DB_PORT') ?: '5432',
            'dbname' => getenv('TEST_DB_NAME') ?: 'strathub_test',
            'user' => getenv('TEST_DB_USER') ?: 'postgres',
            'password' => getenv('TEST_DB_PASSWORD') ?: 'postgres',
            'sslmode' => getenv('TEST_DB_SSLMODE') ?: 'disable',
        ];

        $database = new Database($config);
        $pdo = $database->pdo();

        $pdo->exec('DROP SCHEMA IF EXISTS public CASCADE');
        $pdo->exec('CREATE SCHEMA public');

        foreach (['schema.pgsql.sql', 'seeds.sql'] as $file) {
            $path = dirname(__DIR__) . "/database/{$file}";
            $sql = is_readable($path) ? file_get_contents($path) : false;

            if ($sql === false) {
                throw new RuntimeException("Arquivo SQL não encontrado: {$path}");
            }

            $pdo->exec($sql);
        }

        return $database;
    }

    /**
     * Nome do driver em uso, para mensagens de asserção.
     */
    protected function currentDriver(): string
    {
        return Database::connection()->pdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
}
