<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formata a resposta JSON de um produto.
 *
 * Preços são retornados em reais (float) para o frontend.
 * Imagens são retornadas com URL original e miniatura (thumb).
 * Variantes e imagens só são incluídas quando carregadas (evita N+1).
 */
class ProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'description'   => $this->description,
            // Preço convertido de centavos para reais
            'price'         => $this->price / 100,
            'compare_price' => $this->compare_price !== null
                ? $this->compare_price / 100
                : null,
            'sku'           => $this->sku,
            'stock'         => $this->stock,
            'status'        => $this->status,
            'category_id'   => $this->category_id,
            // Imagens incluídas apenas no endpoint de detalhe
            'images'        => $this->getMedia('images')->map(fn ($media) => [
                'id'        => $media->id,
                'url'       => $media->getUrl(),
                'thumb_url' => $media->getUrl('thumb'),
            ]),
            // Variantes incluídas apenas quando carregadas com eager loading
            'variants'      => $this->when(
                $this->relationLoaded('variants'),
                fn () => ProductVariantResource::collection($this->variants)
            ),
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
        ];
    }
}
