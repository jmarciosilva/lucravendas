<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\RemoveCartItem;

use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Orders\Domain\Entities\Cart;
use App\Modules\Orders\Domain\Repositories\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class RemoveCartItemHandler
{
    public function __construct(
        private readonly CartRepositoryInterface $repository,
        private readonly GetCartHandler          $getCartHandler,
    ) {}

    public function handle(string $tenantId, ?string $sessionId, ?int $userId, int $itemId): Cart
    {
        return DB::transaction(function () use ($tenantId, $sessionId, $userId, $itemId) {
            $cart = $this->getCartHandler->handle($tenantId, $sessionId, $userId);

            if ($cart->isEmpty()) {
                throw new RuntimeException('Carrinho vazio.');
            }

            $cart->removeItem($itemId);

            return $this->repository->save($cart);
        });
    }
}
