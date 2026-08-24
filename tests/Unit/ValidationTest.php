<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Validation;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ValidationTest extends TestCase
{
    #[Test]
    public function campo_vazio_falha_em_required(): void
    {
        $validation = Validation::validate(['titulo' => ['required']], ['titulo' => '   ']);

        $this->assertTrue($validation->fails());
        $this->assertSame(['O título é obrigatório.'], $validation->errors()['titulo']);
    }

    #[Test]
    public function mensagem_concorda_com_o_genero_do_campo(): void
    {
        $validation = Validation::validate(['descricao' => ['required']], []);

        $this->assertSame(['A descrição é obrigatória.'], $validation->errors()['descricao']);
    }

    #[Test]
    #[DataProvider('emailsInvalidos')]
    public function email_invalido_e_rejeitado(string $email): void
    {
        $validation = Validation::validate(['email' => ['email']], ['email' => $email]);

        $this->assertTrue($validation->fails());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function emailsInvalidos(): array
    {
        return [
            'sem arroba' => ['gui.example.com'],
            'sem dominio' => ['gui@'],
            'com espaco' => ['gui @example.com'],
        ];
    }

    #[Test]
    public function email_valido_passa(): void
    {
        $validation = Validation::validate(
            ['email' => ['required', 'email']],
            ['email' => 'jogador@strathub.gg'],
        );

        $this->assertTrue($validation->passes());
    }

    #[Test]
    public function min_e_max_contam_caracteres_multibyte(): void
    {
        // "ção" tem 3 caracteres e 5 bytes. Com strlen() em vez de mb_strlen(),
        // esta descrição passaria por um limite mínimo que ela não alcança.
        $validation = Validation::validate(
            ['descricao' => ['min:4']],
            ['descricao' => 'ção'],
        );

        $this->assertTrue($validation->fails());
    }

    #[Test]
    public function strong_exige_caractere_especial(): void
    {
        $semEspecial = Validation::validate(['senha' => ['strong']], ['senha' => 'senhaforte123']);
        $comEspecial = Validation::validate(['senha' => ['strong']], ['senha' => 'senha#forte123']);

        $this->assertTrue($semEspecial->fails());
        $this->assertTrue($comEspecial->passes());
    }

    #[Test]
    public function strong_aceita_acentos_como_letra_nao_como_especial(): void
    {
        // Com a implementação antiga baseada em strpbrk sobre uma lista fixa de
        // símbolos ASCII, um caractere acentuado passava despercebido de um jeito
        // ou de outro. A classe \p{L} do Unicode trata "ã" como letra.
        $validation = Validation::validate(['senha' => ['strong']], ['senha' => 'senhaComÃ']);

        $this->assertTrue($validation->fails());
    }

    #[Test]
    public function in_restringe_aos_valores_permitidos(): void
    {
        $invalido = Validation::validate(['elo' => ['in:ferro,bronze']], ['elo' => 'challenger']);
        $valido = Validation::validate(['elo' => ['in:ferro,bronze']], ['elo' => 'bronze']);

        $this->assertTrue($invalido->fails());
        $this->assertTrue($valido->passes());
    }

    #[Test]
    public function between_valida_faixa_numerica(): void
    {
        $foraDaFaixa = Validation::validate(['avaliacao' => ['between:1,5']], ['avaliacao' => '9']);
        $naFaixa = Validation::validate(['avaliacao' => ['between:1,5']], ['avaliacao' => '4']);

        $this->assertTrue($foraDaFaixa->fails());
        $this->assertTrue($naFaixa->passes());
    }

    #[Test]
    public function unique_recusa_tabela_fora_da_allowlist(): void
    {
        // Identificadores SQL não podem ser parametrizados; a allowlist é o que
        // impede a regra de virar um vetor de injeção.
        $this->expectException(InvalidArgumentException::class);

        Validation::validate(
            ['email' => ['unique:users_secret,token']],
            ['email' => 'x@y.com'],
        );
    }

    #[Test]
    public function regra_desconhecida_falha_alto(): void
    {
        // Um erro de digitação numa regra deve quebrar em desenvolvimento, e não
        // silenciosamente deixar o campo sem validação em produção.
        $this->expectException(InvalidArgumentException::class);

        Validation::validate(['titulo' => ['requiredd']], ['titulo' => '']);
    }
}
