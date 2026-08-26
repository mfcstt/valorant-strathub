<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\DatabaseSessionHandler;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * A sessão vive no banco, não em arquivo - é a peça que corrige o bug em que
 * todo formulário do site devolvia 419 em produção: a Vercel podia atender o
 * GET que gera o token CSRF e o POST que o envia em duas instâncias
 * serverless diferentes, sem disco compartilhado entre elas.
 */
final class DatabaseSessionHandlerTest extends TestCase
{
    private DatabaseSessionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $database = $this->useDatabase();
        $this->handler = new DatabaseSessionHandler($database);
    }

    #[Test]
    public function ler_sessao_inexistente_devolve_string_vazia(): void
    {
        // String vazia (não false) é o que o PHP espera para "sessão nova,
        // ainda sem dados" - false sinalizaria falha de I/O.
        $this->assertSame('', $this->handler->read('id-que-nao-existe'));
    }

    #[Test]
    public function grava_e_le_de_volta(): void
    {
        $this->handler->write('sess-1', 'auth|O:8:"stdClass":0:{}');

        $this->assertSame('auth|O:8:"stdClass":0:{}', $this->handler->read('sess-1'));
    }

    #[Test]
    public function escrita_repetida_atualiza_em_vez_de_duplicar(): void
    {
        // É exatamente o caminho que uma sessão real percorre: escrita no GET
        // que desenha o formulário, escrita de novo no POST que o processa.
        $this->handler->write('sess-1', 'primeira versão');
        $this->handler->write('sess-1', 'segunda versão');

        $this->assertSame('segunda versão', $this->handler->read('sess-1'));
    }

    #[Test]
    public function destroy_remove_a_sessao(): void
    {
        $this->handler->write('sess-1', 'dado');
        $this->handler->destroy('sess-1');

        $this->assertSame('', $this->handler->read('sess-1'));
    }

    #[Test]
    public function gc_remove_apenas_sessoes_expiradas(): void
    {
        $database = $this->useDatabase();
        $handler = new DatabaseSessionHandler($database);

        $handler->write('antiga', 'dado velho');
        $handler->write('recente', 'dado novo');

        // Força a sessão "antiga" a parecer ter sido escrita há muito tempo,
        // sem depender de dormir de verdade no teste.
        $database->execute(
            'UPDATE sessions SET last_activity = :old WHERE id = :id',
            ['old' => time() - 10_000, 'id' => 'antiga'],
        );

        $handler->gc(max_lifetime: 3600);

        $this->assertSame('', $handler->read('antiga'));
        $this->assertSame('dado novo', $handler->read('recente'));
    }

    #[Test]
    public function open_e_close_sempre_confirmam_sucesso(): void
    {
        // O handler não abre nenhum recurso próprio - a conexão já vem pronta
        // via injeção - então não há nada que possa falhar aqui.
        $this->assertTrue($this->handler->open('', 'strathub_session'));
        $this->assertTrue($this->handler->close());
    }
}
