<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Strategy;
use App\Models\User;
use App\Support\Auth;
use App\Support\Database;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Moderação de estratégias.
 *
 * Toda estratégia nova nasce PENDING e só aparece publicamente depois de
 * aprovada - sem isso, qualquer pessoa cadastrada publica direto no site,
 * sem nenhum filtro antes de virar um link compartilhável.
 */
final class ModerationTest extends TestCase
{
    private Database $database;
    private int $authorId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = $this->useDatabase();

        $author = User::create('Autora', 'autora@strathub.test', 'senha#forte1', 'ouro');
        $this->assertNotNull($author);
        $this->authorId = (int) $author->id;
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createStrategy(array $overrides = []): int
    {
        return Strategy::create([
            'title' => 'Smoke duplo no meio',
            'category' => 'ataque',
            'description' => 'Descrição com tamanho suficiente para a validação.',
            'user_id' => $this->authorId,
            'agent_id' => null,
            'map_id' => null,
            ...$overrides,
        ]);
    }

    #[Test]
    public function estrategia_nova_nasce_pendente(): void
    {
        $id = $this->createStrategy();

        $strategy = Strategy::find($id);

        $this->assertNotNull($strategy);
        $this->assertTrue($strategy->isPending());
        $this->assertFalse($strategy->isApproved());
    }

    #[Test]
    public function listagem_publica_so_mostra_aprovadas(): void
    {
        $this->createStrategy(['title' => 'Pendente']);
        $this->createStrategy(['title' => 'Aprovada', 'status' => Strategy::STATUS_APPROVED]);
        $this->createStrategy(['title' => 'Rejeitada', 'status' => Strategy::STATUS_REJECTED]);

        $listing = Strategy::paginate([]);

        $this->assertSame(1, $listing['total']);
        $this->assertSame('Aprovada', $listing['items'][0]->title);
    }

    #[Test]
    public function dono_ve_as_proprias_em_qualquer_status(): void
    {
        $this->createStrategy(['title' => 'Pendente']);
        $this->createStrategy(['title' => 'Aprovada', 'status' => Strategy::STATUS_APPROVED]);
        $this->createStrategy(['title' => 'Rejeitada', 'status' => Strategy::STATUS_REJECTED]);

        $listing = Strategy::paginate(
            ['user_id' => $this->authorId, 'statuses' => Strategy::ALL_STATUSES],
        );

        $this->assertSame(3, $listing['total']);
    }

    #[Test]
    public function find_encontra_independente_do_status(): void
    {
        $id = $this->createStrategy();

        $strategy = Strategy::find($id);

        $this->assertNotNull($strategy);
        $this->assertTrue($strategy->isPending());
    }

    #[Test]
    public function moderate_aprova_e_publica(): void
    {
        $id = $this->createStrategy();

        $this->assertTrue(Strategy::moderate($id, Strategy::STATUS_APPROVED));

        $strategy = Strategy::find($id);
        $this->assertTrue($strategy->isApproved());
        $this->assertNull($strategy->moderation_note);

        $listing = Strategy::paginate([]);
        $this->assertSame(1, $listing['total']);
    }

    #[Test]
    public function moderate_rejeita_com_nota(): void
    {
        $id = $this->createStrategy();

        $this->assertTrue(Strategy::moderate($id, Strategy::STATUS_REJECTED, 'Capa sem relação com o conteúdo.'));

        $strategy = Strategy::find($id);
        $this->assertTrue($strategy->isRejected());
        $this->assertSame('Capa sem relação com o conteúdo.', $strategy->moderation_note);

        // Continua fora da listagem pública.
        $listing = Strategy::paginate([]);
        $this->assertSame(0, $listing['total']);
    }

    #[Test]
    public function moderate_em_id_inexistente_nao_afeta_nada(): void
    {
        $this->assertFalse(Strategy::moderate(999999, Strategy::STATUS_APPROVED));
    }

    #[Test]
    public function editar_reabre_moderacao_e_limpa_a_nota_anterior(): void
    {
        $id = $this->createStrategy();
        Strategy::moderate($id, Strategy::STATUS_REJECTED, 'Descrição confusa.');

        $updated = Strategy::updateOwnedBy($id, $this->authorId, [
            'title' => 'Smoke duplo no meio (revisado)',
            'category' => 'ataque',
            'description' => 'Descrição reescrita, agora bem mais clara e completa.',
            'agent_id' => null,
            'map_id' => null,
        ]);

        $this->assertTrue($updated);

        $strategy = Strategy::find($id);
        $this->assertTrue($strategy->isPending());
        $this->assertNull($strategy->moderation_note);
        $this->assertSame('Smoke duplo no meio (revisado)', $strategy->title);
    }

    #[Test]
    public function editar_estrategia_de_outra_pessoa_nao_afeta_nada(): void
    {
        $id = $this->createStrategy(['status' => Strategy::STATUS_APPROVED]);

        $updated = Strategy::updateOwnedBy($id, $this->authorId + 999, [
            'title' => 'Sequestrada',
            'category' => 'ataque',
            'description' => 'Tentativa de edição por quem não é dono.',
            'agent_id' => null,
            'map_id' => null,
        ]);

        $this->assertFalse($updated);

        $strategy = Strategy::find($id);
        $this->assertTrue($strategy->isApproved());
        $this->assertNotSame('Sequestrada', $strategy->title);
    }

    #[Test]
    public function pending_count_conta_so_as_pendentes(): void
    {
        $this->createStrategy(['title' => 'Pendente 1']);
        $this->createStrategy(['title' => 'Pendente 2']);
        $this->createStrategy(['title' => 'Aprovada', 'status' => Strategy::STATUS_APPROVED]);

        $this->assertSame(2, Strategy::pendingCount());
    }

    #[Test]
    public function auth_isadmin_reflete_a_flag_do_usuario(): void
    {
        $admin = User::create('Admin', 'admin@strathub.test', 'senha#forte1', 'radiante');
        $this->assertNotNull($admin);

        $this->database->execute('UPDATE users SET is_admin = :flag WHERE id = :id', [
            'flag' => $this->currentDriver() === 'pgsql' ? true : 1,
            'id' => (int) $admin->id,
        ]);

        $admin = User::find((int) $admin->id);
        $this->assertNotNull($admin);

        Auth::login($admin, remember: false);

        $this->assertTrue(Auth::isAdmin());
    }

    #[Test]
    public function usuario_comum_nao_e_admin(): void
    {
        $author = User::find($this->authorId);
        $this->assertNotNull($author);

        Auth::login($author, remember: false);

        $this->assertFalse(Auth::isAdmin());
    }
}
