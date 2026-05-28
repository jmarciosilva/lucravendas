<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Identificador único de um tenant.
 *
 * Usa UUID v4 gerado pelo stancl/tenancy. Encapsular o ID como Value Object
 * evita que strings aleatórias sejam passadas onde um TenantId é esperado.
 */
final class TenantId
{
    private function __construct(
        private readonly string $value,
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException('TenantId não pode ser vazio.');
        }
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
