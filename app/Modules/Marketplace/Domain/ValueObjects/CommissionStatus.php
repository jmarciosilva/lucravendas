<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\ValueObjects;

use RuntimeException;

final class CommissionStatus
{
    public const PENDING   = 'pending';
    public const PAID      = 'paid';
    public const CANCELLED = 'cancelled';

    private function __construct(private readonly string $value) {}

    public static function from(string $value): self
    {
        $valid = [self::PENDING, self::PAID, self::CANCELLED];

        if (! in_array($value, $valid, true)) {
            throw new RuntimeException("Status de comissão inválido: '{$value}'.");
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }
}
