<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Csrf;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CsrfTest extends TestCase
{
    #[Test]
    public function token_e_estavel_dentro_da_mesma_sessao(): void
    {
        $this->assertSame(Csrf::token(), Csrf::token());
    }

    #[Test]
    public function token_tem_entropia_suficiente(): void
    {
        // 32 bytes em hexadecimal. Um token curto o bastante para ser adivinhado
        // torna a proteção decorativa.
        $this->assertSame(64, strlen(Csrf::token()));
    }

    #[Test]
    public function verify_aceita_o_token_correto(): void
    {
        $this->assertTrue(Csrf::verify(Csrf::token()));
    }

    #[Test]
    public function verify_recusa_token_errado(): void
    {
        Csrf::token();

        $this->assertFalse(Csrf::verify('token-invalido'));
    }

    #[Test]
    public function verify_recusa_valores_vazios(): void
    {
        Csrf::token();

        $this->assertFalse(Csrf::verify(null));
        $this->assertFalse(Csrf::verify(''));
    }

    #[Test]
    public function verify_recusa_quando_nao_existe_token_na_sessao(): void
    {
        // Sem esta checagem, uma sessão sem token compararia null com null e
        // aceitaria qualquer requisição.
        $this->assertFalse(Csrf::verify('qualquer-coisa'));
    }

    #[Test]
    public function rotate_invalida_o_token_anterior(): void
    {
        $original = Csrf::token();

        Csrf::rotate();

        $this->assertNotSame($original, Csrf::token());
        $this->assertFalse(Csrf::verify($original));
    }

    #[Test]
    public function field_gera_input_oculto_com_valor_escapado(): void
    {
        $field = Csrf::field();

        $this->assertStringContainsString('type="hidden"', $field);
        $this->assertStringContainsString('name="' . Csrf::FIELD . '"', $field);
        $this->assertStringContainsString(Csrf::token(), $field);
    }
}
