<?php

declare(strict_types=1);

namespace Tests;

use App\Support\Database;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base dos testes: banco SQLite temporário e estado global limpo.
 */
abstract class TestCase extends BaseTestCase
{
    private ?string $databasePath = null;

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

        if ($this->databasePath !== null && is_file($this->databasePath)) {
            unlink($this->databasePath);
        }
        $this->databasePath = null;

        parent::tearDown();
    }

    /**
     * Cria um banco SQLite isolado para este teste, com schema e seeds aplicados.
     *
     * Um arquivo temporário em vez de `:memory:` porque o schema é carregado pelo
     * próprio Database ao abrir a conexão, e um arquivo deixa o estado
     * inspecionável quando um teste falha.
     */
    protected function useDatabase(): Database
    {
        $this->databasePath = tempnam(sys_get_temp_dir(), 'strathub-test-') . '.sqlite';

        $database = new Database([
            'driver' => 'sqlite',
            'database' => $this->databasePath,
        ]);

        Database::swap($database);

        return $database;
    }
}
