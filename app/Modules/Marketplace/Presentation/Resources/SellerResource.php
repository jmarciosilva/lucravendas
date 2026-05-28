<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Presentation\Resources;

use App\Modules\Marketplace\Domain\Entities\Seller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Representação pública de um seller para a API.
 *
 * Não expõe dados sensíveis como bank_info ou user_id.
 *
 * @mixin Seller
 */
class SellerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Seller $seller */
        $seller = $this->resource;

        return [
            'id'          => $seller->id(),
            'name'        => $seller->name(),
            'slug'        => $seller->slug(),
            'description' => $seller->description(),
            'status'      => $seller->status()->value(),
        ];
    }
}
