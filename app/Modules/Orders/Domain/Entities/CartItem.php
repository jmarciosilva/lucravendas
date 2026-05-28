<?php

declare(strict_types=1);

namespace App\Modules\Orders\Domain\Entities;

use App\Modules\Catalog\Domain\ValueObjects\Money;

final class CartItem
{
    private function __construct(
        private readonly ?int  $id,
        private readonly int   $cartId,
        private readonly int   $productId,
        private readonly ?int  $variantId,
        private int            $quantity,
        private readonly Money $unitPrice,
        private readonly string $productName,
    ) {}

    public static function create(
        int     $cartId,
        int     $productId,
        ?int    $variantId,
        int     $quantity,
        Money   $unitPrice,
        string  $productName,
    ): self {
        if ($quantity < 1) {
            throw new \RuntimeException('A quantidade do item deve ser ao menos 1.');
        }

        return new self(
            id: null,
            cartId: $cartId,
            productId: $productId,
            variantId: $variantId,
            quantity: $quantity,
            unitPrice: $unitPrice,
            productName: $productName,
        );
    }

    public static function restore(
        int     $id,
        int     $cartId,
        int     $productId,
        ?int    $variantId,
        int     $quantity,
        Money   $unitPrice,
        string  $productName,
    ): self {
        return new self($id, $cartId, $productId, $variantId, $quantity, $unitPrice, $productName);
    }

    public function incrementQuantity(int $amount): void
    {
        $this->quantity += $amount;
    }

    public function updateQuantity(int $quantity): void
    {
        if ($quantity < 1) {
            throw new \RuntimeException('A quantidade do item deve ser ao menos 1.');
        }
        $this->quantity = $quantity;
    }

    public function total(): Money
    {
        return Money::fromCentavos($this->unitPrice->centavos() * $this->quantity);
    }

    public function id(): ?int          { return $this->id; }
    public function cartId(): int        { return $this->cartId; }
    public function productId(): int     { return $this->productId; }
    public function variantId(): ?int    { return $this->variantId; }
    public function quantity(): int      { return $this->quantity; }
    public function unitPrice(): Money   { return $this->unitPrice; }
    public function productName(): string { return $this->productName; }
}
