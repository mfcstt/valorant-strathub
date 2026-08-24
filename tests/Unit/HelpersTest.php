<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HelpersTest extends TestCase
{
    #[Test]
    public function e_escapa_html_e_aspas(): void
    {
        $this->assertSame(
            '&lt;script&gt;alert(1)&lt;/script&gt;',
            e('<script>alert(1)</script>'),
        );

        // ENT_QUOTES cobre aspas simples também: sem isso, um valor interpolado
        // num atributo delimitado por aspas simples escaparia do atributo.
        $this->assertSame('&#039;onload=x&#039;', e("'onload=x'"));
    }

    #[Test]
    public function e_trata_null_e_boolean_como_string_vazia(): void
    {
        $this->assertSame('', e(null));
        $this->assertSame('', e(false));
        $this->assertSame('', e(true));
    }

    #[Test]
    public function e_preserva_acentuacao(): void
    {
        $this->assertSame('estratégia de retake', e('estratégia de retake'));
    }

    #[Test]
    public function old_devolve_o_valor_do_flash_sem_consumir(): void
    {
        flash()->put('formData', ['titulo' => 'Smoke de A curto']);

        $this->assertSame('Smoke de A curto', old('titulo'));
        // Segunda leitura precisa funcionar: o mesmo formulário desenha vários
        // campos numa única renderização.
        $this->assertSame('Smoke de A curto', old('titulo'));
    }

    #[Test]
    public function old_devolve_o_padrao_quando_nao_ha_dado(): void
    {
        $this->assertSame('', old('titulo'));
        $this->assertSame('padrao', old('titulo', 'padrao'));
    }

    #[Test]
    public function query_string_remove_o_parametro_interno_de_rota(): void
    {
        $_GET = ['path' => 'explore', 'pesquisar' => 'jett'];

        $this->assertSame('pesquisar=jett', query_string());
    }

    #[Test]
    public function query_string_aplica_sobrescritas_e_remocoes(): void
    {
        $_GET = ['pesquisar' => 'jett', 'page' => '3'];

        $this->assertSame('pesquisar=jett&page=5', query_string(['page' => 5]));
        $this->assertSame('page=3', query_string(['pesquisar' => null]));
    }

}
