<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formata a resposta JSON de uma variante de produto.
 */
class ProductVariantResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'sku'        => $this->sku,
            // Preço em reais (float) para consumo pelo frontend
            'price'      => $this->price !== null ? $this->price / 100 : null,
            'stock'      => $this->stock,
            'is_active'  => $this->is_active,
            'attributes' => $this->attributes ?? [],
        ];
    }
}
