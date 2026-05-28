<?php

declare(strict_types=1);

namespace App\Modules\Orders\Domain\Entities;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use RuntimeException;

final class Cart
{
    /** @param CartItem[] $items */
    private function __construct(
        private readonly ?int   $id,
        private readonly string $tenantId,
        private readonly ?string $sessionId,
        private readonly ?int   $userId,
        private ?int            $couponId,
        private array           $items,
    ) {}

    public static function create(
        string  $tenantId,
        ?string $sessionId,
        ?int    $userId,
    ): self {
        if ($sessionId === null && $userId === null) {
            throw new RuntimeException('O carrinho requer um session_id ou um user_id.');
        }

        return new self(
            id: null,
            tenantId: $tenantId,
            sessionId: $sessionId,
            userId: $userId,
            couponId: null,
            items: [],
        );
    }

    /** @param CartItem[] $items */
    public static function restore(
        int     $id,
        string  $tenantId,
        ?string $sessionId,
        ?int    $userId,
        ?int    $couponId,
        array   $items,
    ): self {
        return new self($id, $tenantId, $sessionId, $userId, $couponId, $items);
    }

    public function addItem(CartItem $newItem): void
    {
        foreach ($this->items as $existing) {
            if (
                $existing->productId() === $newItem->productId()
                && $existing->variantId() === $newItem->variantId()
            ) {
                $existing->incrementQuantity($newItem->quantity());
                return;
            }
        }

        $this->items[] = $newItem;
    }

    public function removeItem(int $itemId): void
    {
        $this->items = array_values(
            array_filter($this->items, fn (CartItem $i) => $i->id() !== $itemId)
        );
    }

    public function updateItemQuantity(int $itemId, int $quantity): void
    {
        foreach ($this->items as $item) {
            if ($item->id() === $itemId) {
                $item->updateQuantity($quantity);
                return;
            }
        }

        throw new RuntimeException('Item não encontrado no carrinho.');
    }

    public function applyCoupon(int $couponId): void
    {
        $this->couponId = $couponId;
    }

    public function removeCoupon(): void
    {
        $this->couponId = null;
    }

    public function subtotal(): Money
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->total()->centavos();
        }

        return Money::fromCentavos($total);
    }

    public function calculateDiscount(string $couponType, int $couponValue): Money
    {
        $subtotal = $this->subtotal()->centavos();

        if ($couponType === 'percent') {
            return Money::fromCentavos((int) round($subtotal * $couponValue / 100));
        }

        // fixed: desconto não pode superar o subtotal
        return Money::fromCentavos(min($couponValue, $subtotal));
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function hasItemForProduct(int $productId, ?int $variantId): bool
    {
        foreach ($this->items as $item) {
            if ($item->productId() === $productId && $item->variantId() === $variantId) {
                return true;
            }
        }

        return false;
    }

    public function id(): ?int           { return $this->id; }
    public function tenantId(): string   { return $this->tenantId; }
    public function sessionId(): ?string { return $this->sessionId; }
    public function userId(): ?int       { return $this->userId; }
    public function couponId(): ?int     { return $this->couponId; }

    /** @return CartItem[] */
    public function items(): array       { return $this->items; }
}
