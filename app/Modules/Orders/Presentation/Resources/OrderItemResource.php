<?php

declare(strict_types=1);

namespace App\Modules\Orders\Presentation\Resources;

use App\Modules\Orders\Domain\Entities\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderItem */
class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var OrderItem $item */
        $item = $this->resource;

        return [
            'product_id' => $item->productId(),
            'name'       => $item->name(),
            'sku'        => $item->sku(),
            'unit_price' => $item->unitPrice()->reais(),
            'quantity'   => $item->quantity(),
            'total'      => $item->total()->reais(),
        ];
    }
}
