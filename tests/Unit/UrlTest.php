<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Url;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UrlTest extends TestCase
{
    #[Test]
    #[DataProvider('destinosExternos')]
    public function destino_externo_cai_no_fallback(string $candidate): void
    {
        $this->assertSame('/explore', Url::safeInternalPath($candidate));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function destinosExternos(): array
    {
        return [
            'url absoluta https' => ['https://exemplo-malicioso.test/phishing'],
            'url absoluta http' => ['http://exemplo-malicioso.test'],
            'protocolo relativo' => ['//exemplo-malicioso.test/phishing'],
            'esquema javascript' => ['javascript:alert(1)'],
            'esquema data' => ['data:text/html,<script>alert(1)</script>'],
            'injecao de cabecalho' => ["/explore\r\nSet-Cookie: sessao=roubada"],
            'byte nulo' => ["/explore\0/evil"],
            'string vazia' => [''],
            'apenas espacos' => ['   '],
        ];
    }

    #[Test]
    #[DataProvider('destinosInternos')]
    public function caminho_interno_e_preservado(string $candidate, string $expected): void
    {
        $this->assertSame($expected, Url::safeInternalPath($candidate));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function destinosInternos(): array
    {
        return [
            'raiz' => ['/', '/'],
            'rota simples' => ['/favorites', '/favorites'],
            'com query string' => ['/explore?page=2&ordenar=recentes', '/explore?page=2&ordenar=recentes'],
            'com espacos nas pontas' => ['  /my-strategies  ', '/my-strategies'],
        ];
    }

    #[Test]
    public function null_cai_no_fallback(): void
    {
        $this->assertSame('/explore', Url::safeInternalPath(null));
    }

    #[Test]
    public function fallback_customizado_e_respeitado(): void
    {
        $this->assertSame('/login', Url::safeInternalPath('https://externo.test', '/login'));
    }
}
