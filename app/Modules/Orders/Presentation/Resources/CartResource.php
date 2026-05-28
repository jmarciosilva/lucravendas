<?php

declare(strict_types=1);

namespace App\Modules\Orders\Presentation\Resources;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Orders\Domain\Entities\Cart;
use App\Modules\Orders\Infrastructure\Models\CouponModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Cart */
class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Cart $cart */
        $cart = $this->resource;

        $discount  = Money::fromCentavos(0);
        $couponData = null;

        if ($cart->couponId() !== null) {
            $coupon = CouponModel::find($cart->couponId());
            if ($coupon) {
                $discount = $cart->calculateDiscount($coupon->type, $coupon->value);
                $couponData = [
                    'code'     => $coupon->code,
                    'type'     => $coupon->type,
                    'value'    => $coupon->value,
                    'discount' => $discount->reais(),
                ];
            }
        }

        $subtotal = $cart->subtotal();
        $total    = Money::fromCentavos(max(0, $subtotal->centavos() - $discount->centavos()));

        return [
            'id'        => $cart->id(),
            'items'     => CartItemResource::collection($cart->items()),
            'coupon'    => $couponData,
            'subtotal'  => $subtotal->reais(),
            'discount'  => $discount->reais(),
            'total'     => $total->reais(),
            'is_empty'  => $cart->isEmpty(),
        ];
    }
}
