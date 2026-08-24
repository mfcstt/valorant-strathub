<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Strategy;
use App\Models\User;
use App\Support\Database;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * A ordenação precisa acontecer no banco, antes do recorte da paginação.
 *
 * O bug que estes testes travam: a versão anterior paginava por `created_at` e
 * só depois reordenava, em PHP, os itens já trazidos. Com 12 estratégias e 10 por
 * página, a melhor avaliada de todas podia estar na página 2 e nunca aparecer no
 * topo de "Mais estrelas".
 */
final class StrategyOrderingTest extends TestCase
{
    private Database $database;
    private int $authorId;
    private int $raterId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = $this->useDatabase();

        $author = User::create('Autor', 'autor@strathub.test', 'senha#forte1', 'ouro');
        $rater = User::create('Avaliador', 'avaliador@strathub.test', 'senha#forte1', 'prata');

        $this->assertNotNull($author);
        $this->assertNotNull($rater);

        $this->authorId = (int) $author->id;
        $this->raterId = (int) $rater->id;
    }

    #[Test]
    public function melhor_avaliada_aparece_na_primeira_pagina_mesmo_sendo_a_mais_antiga(): void
    {
        // 12 estratégias, com 10 por página. A primeira criada (portanto a última
        // por data) recebe a melhor nota.
        $ids = [];
        for ($i = 1; $i <= 12; $i++) {
            $ids[$i] = $this->createStrategy("Estratégia {$i}", "2026-01-{$i}");
        }

        $this->rate($ids[1], 5);
        $this->rate($ids[12], 2);

        $result = Strategy::paginate(['order' => 'mais_estrelas'], page: 1, perPage: 10);

        $this->assertCount(10, $result['items']);
        $this->assertSame(12, $result['total']);
        $this->assertSame(2, $result['pages']);
        $this->assertSame('Estratégia 1', $result['items'][0]->title);
    }

    #[Test]
    public function menos_estrelas_inverte_a_ordem(): void
    {
        $primeiro = $this->createStrategy('Nota alta', '2026-01-01');
        $segundo = $this->createStrategy('Nota baixa', '2026-01-02');

        $this->rate($primeiro, 5);
        $this->rate($segundo, 1);

        $result = Strategy::paginate(['order' => 'menos_estrelas'], page: 1, perPage: 10);

        $this->assertSame('Nota baixa', $result['items'][0]->title);
    }

    #[Test]
    public function recentes_ordena_por_data_de_criacao(): void
    {
        $this->createStrategy('Antiga', '2026-01-01');
        $this->createStrategy('Nova', '2026-06-01');

        $result = Strategy::paginate(['order' => 'recentes'], page: 1, perPage: 10);

        $this->assertSame('Nova', $result['items'][0]->title);
    }

    #[Test]
    public function ordenacao_desconhecida_cai_no_padrao_em_vez_de_entrar_no_sql(): void
    {
        // O valor vem da query string. Sem a allowlist, isto seria injeção de SQL
        // na cláusula ORDER BY, que não aceita parâmetro vinculado.
        $this->createStrategy('Única', '2026-01-01');

        $result = Strategy::paginate(
            ['order' => 'title; DROP TABLE strategies; --'],
            page: 1,
            perPage: 10,
        );

        $this->assertCount(1, $result['items']);
        $this->assertSame(
            1,
            (int) $this->database->scalar('SELECT COUNT(*) FROM strategies'),
        );
    }

    #[Test]
    public function pagina_alem_do_limite_e_presa_na_ultima(): void
    {
        $this->createStrategy('Única', '2026-01-01');

        $result = Strategy::paginate([], page: 99, perPage: 10);

        $this->assertSame(1, $result['page']);
        $this->assertCount(1, $result['items']);
    }

    #[Test]
    public function media_e_contagem_de_avaliacoes_vem_calculadas(): void
    {
        $id = $this->createStrategy('Com notas', '2026-01-01');

        $this->rate($id, 4);
        $this->rate($id, 2, $this->authorId);

        $strategy = Strategy::find($id);

        $this->assertNotNull($strategy);
        $this->assertSame(3.0, $strategy->ratingAverage());
        $this->assertSame(2, $strategy->ratingsCount());
    }

    #[Test]
    public function estrategia_sem_avaliacao_tem_media_zero_e_nao_null(): void
    {
        $id = $this->createStrategy('Sem notas', '2026-01-01');

        $strategy = Strategy::find($id);

        $this->assertNotNull($strategy);
        $this->assertSame(0.0, $strategy->ratingAverage());
        $this->assertSame(0, $strategy->ratingsCount());
    }

    private function createStrategy(string $title, string $createdAt): int
    {
        $id = Strategy::create([
            'title' => $title,
            'category' => 'ataque',
            'description' => 'Descrição suficientemente longa para a validação.',
            'user_id' => $this->authorId,
            'agent_id' => null,
            'map_id' => null,
        ]);

        // A data é fixada explicitamente para os testes de ordenação serem
        // determinísticos, em vez de depender do relógio.
        $this->database->execute(
            'UPDATE strategies SET created_at = :created_at WHERE id = :id',
            ['created_at' => $createdAt . ' 12:00:00', 'id' => $id],
        );

        return $id;
    }

    private function rate(int $strategyId, int $value, ?int $userId = null): void
    {
        $this->database->execute(
            'INSERT INTO ratings (user_id, strategy_id, rating, comment)
             VALUES (:user_id, :strategy_id, :rating, :comment)',
            [
                'user_id' => $userId ?? $this->raterId,
                'strategy_id' => $strategyId,
                'rating' => $value,
                'comment' => 'Comentário de teste.',
            ],
        );
    }
}
