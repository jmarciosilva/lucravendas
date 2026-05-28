<?php

declare(strict_types=1);

namespace App\Modules\Orders\Infrastructure\Repositories;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Orders\Domain\Entities\Cart;
use App\Modules\Orders\Domain\Entities\CartItem;
use App\Modules\Orders\Domain\Repositories\CartRepositoryInterface;
use App\Modules\Orders\Infrastructure\Models\CartItemModel;
use App\Modules\Orders\Infrastructure\Models\CartModel;

class EloquentCartRepository implements CartRepositoryInterface
{
    public function findBySessionId(string $sessionId, string $tenantId): ?Cart
    {
        $model = CartModel::with('items.product')
            ->where('session_id', $sessionId)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByUserId(int $userId, string $tenantId): ?Cart
    {
        $model = CartModel::with('items.product')
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function save(Cart $cart): Cart
    {
        if ($cart->id() === null) {
            $model = CartModel::create([
                'tenant_id'  => $cart->tenantId(),
                'session_id' => $cart->sessionId(),
                'user_id'    => $cart->userId(),
                'coupon_id'  => $cart->couponId(),
            ]);
        } else {
            $model = CartModel::findOrFail($cart->id());
            $model->update(['coupon_id' => $cart->couponId()]);
        }

        // Sincroniza os itens: remove todos e recria (simples e confiável para o MVP)
        CartItemModel::where('cart_id', $model->id)->delete();

        foreach ($cart->items() as $item) {
            CartItemModel::create([
                'cart_id'    => $model->id,
                'product_id' => $item->productId(),
                'variant_id' => $item->variantId(),
                'quantity'   => $item->quantity(),
                'unit_price' => $item->unitPrice()->centavos(),
            ]);
        }

        $model->load('items.product');

        return $this->toDomain($model);
    }

    public function delete(int $cartId): void
    {
        CartItemModel::where('cart_id', $cartId)->delete();
        CartModel::destroy($cartId);
    }

    private function toDomain(CartModel $model): Cart
    {
        $items = $model->items->map(function (CartItemModel $item) use ($model): CartItem {
            return CartItem::restore(
                id: $item->id,
                cartId: $model->id,
                productId: $item->product_id,
                variantId: $item->variant_id,
                quantity: $item->quantity,
                unitPrice: Money::fromCentavos($item->unit_price),
                productName: $item->product?->name ?? '',
            );
        })->all();

        return Cart::restore(
            id: $model->id,
            tenantId: $model->tenant_id,
            sessionId: $model->session_id,
            userId: $model->user_id,
            couponId: $model->coupon_id,
            items: $items,
        );
    }
}
