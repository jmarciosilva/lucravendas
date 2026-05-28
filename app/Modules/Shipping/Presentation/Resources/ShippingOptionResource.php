<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Presentation\Resources;

use App\Modules\Shipping\Domain\ValueObjects\ShippingOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ShippingOption
 */
class ShippingOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ShippingOption $option */
        $option = $this->resource;

        return [
            'id'               => $option->id,
            'name'             => $option->name,
            'carrier'          => $option->carrier,
            'price_centavos'   => $option->priceCentavos,
            'price_formatted'  => 'R$ ' . number_format($option->priceCentavos / 100, 2, ',', '.'),
            'min_days'         => $option->minDays,
            'max_days'         => $option->maxDays,
            'is_free_shipping' => $option->isFreeShipping,
        ];
    }
}
