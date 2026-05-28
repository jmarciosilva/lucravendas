<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\ValueObjects;

use RuntimeException;

final class PayoutStatus
{
    public const PENDING    = 'pending';
    public const PROCESSING = 'processing';
    public const PAID       = 'paid';
    public const FAILED     = 'failed';

    private function __construct(private readonly string $value) {}

    public static function from(string $value): self
    {
        $valid = [self::PENDING, self::PROCESSING, self::PAID, self::FAILED];

        if (! in_array($value, $valid, true)) {
            throw new RuntimeException("Status de repasse inválido: '{$value}'.");
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
