<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\RemoveCoupon;

use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Orders\Domain\Entities\Cart;
use App\Modules\Orders\Domain\Repositories\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class RemoveCouponHandler
{
    public function __construct(
        private readonly CartRepositoryInterface $repository,
        private readonly GetCartHandler          $getCartHandler,
    ) {}

    public function handle(string $tenantId, ?string $sessionId, ?int $userId): Cart
    {
        return DB::transaction(function () use ($tenantId, $sessionId, $userId) {
            $cart = $this->getCartHandler->handle($tenantId, $sessionId, $userId);
            $cart->removeCoupon();
            return $this->repository->save($cart);
        });
    }
}
