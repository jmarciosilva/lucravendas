<?php

declare(strict_types=1);

namespace App\Modules\Orders\Domain\ValueObjects;

use RuntimeException;

final class PaymentStatus
{
    public const PENDING  = 'pending';
    public const PAID     = 'paid';
    public const FAILED   = 'failed';
    public const REFUNDED = 'refunded';

    private function __construct(private readonly string $value) {}

    public static function from(string $value): self
    {
        $valid = [self::PENDING, self::PAID, self::FAILED, self::REFUNDED];

        if (! in_array($value, $valid, true)) {
            throw new RuntimeException("Status de pagamento inválido: '{$value}'.");
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPaid(): bool
    {
        return $this->value === self::PAID;
    }
}
