<?php

declare(strict_types=1);

namespace App\Modules\Orders\Domain\ValueObjects;

use RuntimeException;

final class OrderStatus
{
    public const PENDING    = 'pending';
    public const CONFIRMED  = 'confirmed';
    public const PROCESSING = 'processing';
    public const SHIPPED    = 'shipped';
    public const DELIVERED  = 'delivered';
    public const CANCELLED  = 'cancelled';
    public const REFUNDED   = 'refunded';

    private static array $validTransitions = [
        self::PENDING    => [self::CONFIRMED, self::CANCELLED],
        self::CONFIRMED  => [self::PROCESSING, self::CANCELLED],
        self::PROCESSING => [self::SHIPPED, self::CANCELLED],
        self::SHIPPED    => [self::DELIVERED],
        self::DELIVERED  => [self::REFUNDED],
        self::CANCELLED  => [],
        self::REFUNDED   => [],
    ];

    private function __construct(private readonly string $value) {}

    public static function from(string $value): self
    {
        $valid = [
            self::PENDING, self::CONFIRMED, self::PROCESSING,
            self::SHIPPED, self::DELIVERED, self::CANCELLED, self::REFUNDED,
        ];

        if (! in_array($value, $valid, true)) {
            throw new RuntimeException("Status de pedido inválido: '{$value}'.");
        }

        return new self($value);
    }

    public function transitionTo(string $next): self
    {
        if (! in_array($next, self::$validTransitions[$this->value], true)) {
            throw new RuntimeException(
                "Transição de status inválida: '{$this->value}' → '{$next}'."
            );
        }

        return new self($next);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function is(string $status): bool
    {
        return $this->value === $status;
    }
}
