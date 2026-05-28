<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Plano contratado pelo tenant.
 *
 * Centraliza as regras de quais planos existem, evitando strings mágicas
 * espalhadas pelo código.
 */
final class TenantPlan
{
    public const FREE       = 'free';
    public const STARTER    = 'starter';
    public const GROWTH     = 'growth';
    public const ENTERPRISE = 'enterprise';

    private const VALID_PLANS = [
        self::FREE,
        self::STARTER,
        self::GROWTH,
        self::ENTERPRISE,
    ];

    private function __construct(
        private readonly string $value,
    ) {
        if (! in_array($value, self::VALID_PLANS, strict: true)) {
            throw new InvalidArgumentException("Plano inválido: {$value}.");
        }
    }

    public static function fromString(string $plan): self
    {
        return new self($plan);
    }

    public static function free(): self
    {
        return new self(self::FREE);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isFree(): bool
    {
        return $this->value === self::FREE;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
