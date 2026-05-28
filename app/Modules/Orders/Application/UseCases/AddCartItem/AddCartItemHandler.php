<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\AddCartItem;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Orders\Domain\Entities\Cart;
use App\Modules\Orders\Domain\Entities\CartItem;
use App\Modules\Orders\Domain\Repositories\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class AddCartItemHandler
{
    public function __construct(
        private readonly CartRepositoryInterface $repository,
        private readonly GetCartHandler          $getCartHandler,
    ) {}

    public function handle(AddCartItemCommand $command): Cart
    {
        return DB::transaction(function () use ($command) {
            $product = ProductModel::query()
                ->where('id', $command->productId)
                ->where('tenant_id', $command->tenantId)
                ->where('status', 'active')
                ->first();

            if ($product === null) {
                throw new RuntimeException('Produto não encontrado ou inativo.');
            }

            if ($product->stock < $command->quantity) {
                throw new RuntimeException(
                    "Estoque insuficiente. Disponível: {$product->stock} unidade(s)."
                );
            }

            $cart = $this->getCartHandler->handle(
                $command->tenantId,
                $command->sessionId,
                $command->userId,
            );

            $item = CartItem::create(
                cartId: $cart->id() ?? 0,
                productId: $command->productId,
                variantId: $command->variantId,
                quantity: $command->quantity,
                unitPrice: Money::fromCentavos($product->price),
                productName: $product->name,
            );

            $cart->addItem($item);

            return $this->repository->save($cart);
        });
    }
}
