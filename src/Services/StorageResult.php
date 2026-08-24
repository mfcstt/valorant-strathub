<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MediaFile;

/**
 * Resultado de um envio de arquivo: o registro salvo ou o motivo da recusa.
 *
 * Existe para que o controller possa mostrar à pessoa o que exatamente
 * aconteceu ("Formato de imagem não suportado") em vez de um `false` genérico
 * que só dava para traduzir como "tente novamente".
 */
final class StorageResult
{
    private function __construct(
        public readonly bool $ok,
        public readonly ?MediaFile $file,
        public readonly ?string $error,
    ) {
    }

    public static function success(MediaFile $file): self
    {
        return new self(true, $file, null);
    }

    public static function failure(string $error): self
    {
        return new self(false, null, $error);
    }

    /**
     * Id do registro criado, ou null quando o envio falhou.
     */
    public function id(): ?int
    {
        return $this->file !== null ? (int) $this->file->id : null;
    }
}
