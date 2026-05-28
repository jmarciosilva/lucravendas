<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formata a resposta JSON de uma categoria.
 * Inclui filhos recursivamente quando carregados com eager loading.
 */
class CategoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'parent_id'  => $this->parent_id,
            'sort_order' => $this->sort_order,
            // Inclui filhos apenas quando já foram carregados (evita N+1)
            'children'   => $this->when(
                $this->relationLoaded('children'),
                fn () => CategoryResource::collection($this->children)
            ),
        ];
    }
}
