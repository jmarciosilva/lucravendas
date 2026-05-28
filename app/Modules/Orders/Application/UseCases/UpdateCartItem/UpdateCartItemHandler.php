<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\UpdateCartItem;

use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Orders\Domain\Entities\Cart;
use App\Modules\Orders\Domain\Repositories\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class UpdateCartItemHandler
{
    public function __construct(
        private readonly CartRepositoryInterface $repository,
        private readonly GetCartHandler          $getCartHandler,
    ) {}

    public function handle(UpdateCartItemCommand $command): Cart
    {
        return DB::transaction(function () use ($command) {
            $cart = $this->getCartHandler->handle(
                $command->tenantId,
                $command->sessionId,
                $command->userId,
            );

            if ($cart->isEmpty()) {
                throw new RuntimeException('Carrinho vazio.');
            }

            if ($command->quantity === 0) {
                $cart->removeItem($command->itemId);
            } else {
                $cart->updateItemQuantity($command->itemId, $command->quantity);
            }

            return $this->repository->save($cart);
        });
    }
}
