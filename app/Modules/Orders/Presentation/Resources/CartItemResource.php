<?php

declare(strict_types=1);

namespace App\Modules\Orders\Presentation\Resources;

use App\Modules\Orders\Domain\Entities\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CartItem */
class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var CartItem $item */
        $item = $this->resource;

        return [
            'id'          => $item->id(),
            'product_id'  => $item->productId(),
            'product_name'=> $item->productName(),
            'variant_id'  => $item->variantId(),
            'quantity'    => $item->quantity(),
            'unit_price'  => $item->unitPrice()->reais(),
            'total'       => $item->total()->reais(),
        ];
    }
}
