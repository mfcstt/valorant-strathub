<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Rating;
use App\Models\Strategy;
use App\Models\User;
use App\Support\Database;
use PHPUnit\Framework\Attributes\Test;
use PDOException;
use Tests\TestCase;

/**
 * Favoritas, avaliações e posse de estratégia.
 */
final class StrategyInteractionTest extends TestCase
{
    private Database $database;
    private int $authorId;
    private int $otherId;
    private int $strategyId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = $this->useDatabase();

        $author = User::create('Autora', 'autora@strathub.test', 'senha#forte1', 'ouro');
        $other = User::create('Outro', 'outro@strathub.test', 'senha#forte1', 'ferro');

        $this->assertNotNull($author);
        $this->assertNotNull($other);

        $this->authorId = (int) $author->id;
        $this->otherId = (int) $other->id;

        $this->strategyId = Strategy::create([
            'title' => 'Smoke duplo no meio',
            'category' => 'ataque',
            'description' => 'Descrição com tamanho suficiente para a validação.',
            'user_id' => $this->authorId,
            'agent_id' => null,
            'map_id' => null,
        ]);
    }

    #[Test]
    public function toggle_de_favorito_alterna_o_estado(): void
    {
        $this->assertFalse(Favorite::exists($this->otherId, $this->strategyId));

        $this->assertTrue(Favorite::toggle($this->otherId, $this->strategyId));
        $this->assertTrue(Favorite::exists($this->otherId, $this->strategyId));

        $this->assertFalse(Favorite::toggle($this->otherId, $this->strategyId));
        $this->assertFalse(Favorite::exists($this->otherId, $this->strategyId));
    }

    #[Test]
    public function is_favorite_vem_resolvido_na_listagem(): void
    {
        Favorite::toggle($this->otherId, $this->strategyId);

        // Para quem favoritou.
        $listing = Strategy::paginate([], viewerId: $this->otherId);
        $this->assertTrue($listing['items'][0]->isFavorite());

        // Para outra pessoa, a mesma estratégia não está favoritada.
        $listing = Strategy::paginate([], viewerId: $this->authorId);
        $this->assertFalse($listing['items'][0]->isFavorite());

        // E para visitante não autenticado.
        $listing = Strategy::paginate([], viewerId: null);
        $this->assertFalse($listing['items'][0]->isFavorite());
    }

    #[Test]
    public function filtro_de_favoritas_lista_somente_as_do_usuario(): void
    {
        $outra = Strategy::create([
            'title' => 'Retake de B',
            'category' => 'retake',
            'description' => 'Descrição com tamanho suficiente para a validação.',
            'user_id' => $this->authorId,
            'agent_id' => null,
            'map_id' => null,
        ]);

        Favorite::toggle($this->otherId, $outra);

        $listing = Strategy::paginate(['favorited_by' => $this->otherId], viewerId: $this->otherId);

        $this->assertSame(1, $listing['total']);
        $this->assertSame('Retake de B', $listing['items'][0]->title);
    }

    #[Test]
    public function avaliacao_repetida_atualiza_em_vez_de_duplicar(): void
    {
        $criou = Rating::upsert($this->otherId, $this->strategyId, 3, 'Boa.');
        $this->assertFalse($criou, 'A primeira chamada deve criar, não atualizar.');

        $atualizou = Rating::upsert($this->otherId, $this->strategyId, 5, 'Melhor do que pensei.');
        $this->assertTrue($atualizou);

        $this->assertSame(1, (int) $this->database->scalar('SELECT COUNT(*) FROM ratings'));

        $rating = Rating::findByUserAndStrategy($this->otherId, $this->strategyId);
        $this->assertNotNull($rating);
        $this->assertSame(5, $rating->value());
        $this->assertSame('Melhor do que pensei.', $rating->comment);
    }

    #[Test]
    public function nota_fora_da_faixa_e_recusada_pelo_banco(): void
    {
        // Defesa em profundidade: a validação já barra, mas a constraint garante
        // que nenhum caminho de código consiga gravar uma nota inválida.
        $this->expectException(PDOException::class);

        $this->database->execute(
            'INSERT INTO ratings (user_id, strategy_id, rating, comment) VALUES (:u, :s, :r, :c)',
            ['u' => $this->otherId, 's' => $this->strategyId, 'r' => 99, 'c' => 'x'],
        );
    }

    #[Test]
    public function exclusao_exige_que_a_estrategia_seja_do_usuario(): void
    {
        $this->assertFalse(Strategy::deleteOwnedBy($this->strategyId, $this->otherId));
        $this->assertNotNull(Strategy::find($this->strategyId));

        $this->assertTrue(Strategy::deleteOwnedBy($this->strategyId, $this->authorId));
        $this->assertNull(Strategy::find($this->strategyId));
    }

    #[Test]
    public function excluir_estrategia_remove_avaliacoes_e_favoritas_em_cascata(): void
    {
        Rating::upsert($this->otherId, $this->strategyId, 4, 'Funciona.');
        Favorite::toggle($this->otherId, $this->strategyId);

        Strategy::deleteOwnedBy($this->strategyId, $this->authorId);

        $this->assertSame(0, (int) $this->database->scalar('SELECT COUNT(*) FROM ratings'));
        $this->assertSame(0, (int) $this->database->scalar('SELECT COUNT(*) FROM favorites'));
    }

    #[Test]
    public function busca_escapa_curingas_do_like(): void
    {
        Strategy::create([
            'title' => 'Taxa de 100% de acerto',
            'category' => 'defesa',
            'description' => 'Descrição com tamanho suficiente para a validação.',
            'user_id' => $this->authorId,
            'agent_id' => null,
            'map_id' => null,
        ]);

        // Sem escapar, "%" casaria com qualquer coisa e traria as duas
        // estratégias em vez de só a que contém o texto literal.
        $listing = Strategy::paginate(['search' => '100%']);

        $this->assertSame(1, $listing['total']);
        $this->assertSame('Taxa de 100% de acerto', $listing['items'][0]->title);
    }

    #[Test]
    public function curinga_isolado_na_busca_nao_casa_com_tudo(): void
    {
        // Buscar só "%" ou "_" com o escape ausente devolveria a tabela inteira.
        // Nenhum título dos dados deste teste contém esses caracteres.
        foreach (['%', '_', '%%', '_%'] as $needle) {
            $listing = Strategy::paginate(['search' => $needle]);

            $this->assertSame(
                0,
                $listing['total'],
                "A busca por '{$needle}' deveria tratar o curinga como literal.",
            );
        }
    }

    #[Test]
    public function busca_com_termo_repetido_funciona_em_todos_os_drivers(): void
    {
        // Este teste existe por causa de um bug que só aparecia no PostgreSQL: o
        // parser do PDO, ao reescrever :search para $1, engasgava com a cláusula
        // ESCAPE que usava barra invertida e deixava as ocorrências seguintes do
        // parâmetro sem substituir. A consulta usa :search quatro vezes, então
        // qualquer regressão nesse ponto derruba este teste.
        $listing = Strategy::paginate(['search' => 'smoke']);

        $this->assertSame(1, $listing['total'], 'driver: ' . $this->currentDriver());
        $this->assertStringContainsStringIgnoringCase('smoke', (string) $listing['items'][0]->title);
    }

    #[Test]
    public function busca_encontra_por_titulo_parcial_ignorando_caixa(): void
    {
        $listing = Strategy::paginate(['search' => 'SMOKE']);

        $this->assertSame(1, $listing['total']);
    }

    #[Test]
    public function contagem_de_favoritas_por_usuario(): void
    {
        Favorite::toggle($this->otherId, $this->strategyId);

        $this->assertSame(1, Favorite::countForUser($this->otherId));
        $this->assertSame(0, Favorite::countForUser($this->authorId));
    }

    #[Test]
    public function apagar_usuario_remove_as_estrategias_dele(): void
    {
        User::delete($this->authorId);

        $this->assertNull(Strategy::find($this->strategyId));
    }
}
