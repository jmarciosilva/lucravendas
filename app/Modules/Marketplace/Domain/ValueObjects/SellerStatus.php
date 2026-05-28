<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\ValueObjects;

use RuntimeException;

final class SellerStatus
{
    public const PENDING   = 'pending';
    public const ACTIVE    = 'active';
    public const SUSPENDED = 'suspended';

    private function __construct(private readonly string $value) {}

    public static function from(string $value): self
    {
        $valid = [self::PENDING, self::ACTIVE, self::SUSPENDED];

        if (! in_array($value, $valid, true)) {
            throw new RuntimeException("Status de seller inválido: '{$value}'.");
        }

        return new self($value);
    }

    public function approve(): self
    {
        if ($this->value === self::SUSPENDED) {
            throw new RuntimeException('Seller suspenso não pode ser aprovado diretamente. Reative primeiro.');
        }

        return new self(self::ACTIVE);
    }

    public function suspend(): self
    {
        if ($this->value === self::PENDING) {
            throw new RuntimeException('Seller pendente deve ser aprovado ou recusado, não suspenso.');
        }

        return new self(self::SUSPENDED);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }
}
