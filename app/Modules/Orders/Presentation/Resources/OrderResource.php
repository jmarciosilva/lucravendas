<?php

declare(strict_types=1);

namespace App\Modules\Orders\Presentation\Resources;

use App\Modules\Orders\Domain\Entities\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Order $order */
        $order = $this->resource;

        return [
            'id'             => $order->id(),
            'status'         => $order->status()->value(),
            'payment_method' => $order->paymentMethod(),
            'payment_status' => $order->paymentStatus()->value(),
            'subtotal'       => $order->subtotal()->reais(),
            'discount'       => $order->discount()->reais(),
            'shipping_cost'  => $order->shippingCost()->reais(),
            'total'          => $order->total()->reais(),
            'notes'          => $order->notes(),
            'items'          => OrderItemResource::collection($order->items()),
        ];
    }
}
