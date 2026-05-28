<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\ApplyCoupon;

use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Orders\Domain\Entities\Cart;
use App\Modules\Orders\Domain\Repositories\CartRepositoryInterface;
use App\Modules\Orders\Infrastructure\Models\CouponModel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ApplyCouponHandler
{
    public function __construct(
        private readonly CartRepositoryInterface $repository,
        private readonly GetCartHandler          $getCartHandler,
    ) {}

    public function handle(ApplyCouponCommand $command): Cart
    {
        return DB::transaction(function () use ($command) {
            $cart = $this->getCartHandler->handle(
                $command->tenantId,
                $command->sessionId,
                $command->userId,
            );

            if ($cart->isEmpty()) {
                throw new RuntimeException('Adicione produtos ao carrinho antes de aplicar um cupom.');
            }

            $coupon = CouponModel::where('code', mb_strtoupper($command->couponCode))
                ->where('tenant_id', $command->tenantId)
                ->first();

            if ($coupon === null) {
                throw new RuntimeException('Cupom não encontrado.');
            }

            if (! $coupon->isValid($cart->subtotal()->centavos())) {
                if ($cart->subtotal()->centavos() < $coupon->min_order_value) {
                    $min = number_format($coupon->min_order_value / 100, 2, ',', '.');
                    throw new RuntimeException(
                        "Este cupom exige pedido mínimo de R$ {$min}."
                    );
                }
                throw new RuntimeException('Cupom inválido, expirado ou com limite de uso atingido.');
            }

            $cart->applyCoupon($coupon->id);

            return $this->repository->save($cart);
        });
    }
}
