<?php

declare(strict_types=1);

namespace App\Modules\Orders\Domain\Entities;

use App\Modules\Catalog\Domain\ValueObjects\Money;

final class OrderItem
{
    public function __construct(
        private readonly ?int   $id,
        private readonly int    $orderId,
        private readonly int    $productId,
        private readonly ?int   $variantId,
        private readonly string $name,
        private readonly ?string $sku,
        private readonly Money  $unitPrice,
        private readonly int    $quantity,
    ) {}

    public static function create(
        int     $orderId,
        int     $productId,
        ?int    $variantId,
        string  $name,
        ?string $sku,
        Money   $unitPrice,
        int     $quantity,
    ): self {
        return new self(null, $orderId, $productId, $variantId, $name, $sku, $unitPrice, $quantity);
    }

    public function total(): Money
    {
        return Money::fromCentavos($this->unitPrice->centavos() * $this->quantity);
    }

    public function id(): ?int         { return $this->id; }
    public function orderId(): int     { return $this->orderId; }
    public function productId(): int   { return $this->productId; }
    public function variantId(): ?int  { return $this->variantId; }
    public function name(): string     { return $this->name; }
    public function sku(): ?string     { return $this->sku; }
    public function unitPrice(): Money { return $this->unitPrice; }
    public function quantity(): int    { return $this->quantity; }
}
