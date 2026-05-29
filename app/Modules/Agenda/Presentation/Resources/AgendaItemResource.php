<?php

declare(strict_types=1);

namespace App\Modules\Agenda\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource para serialização de itens de agenda.
 *
 * Expõe os campos principais incluindo labels traduzidos,
 * informações de vagas e preço em reais.
 */
final class AgendaItemResource extends JsonResource
{
    /**
     * Mapeia os tipos de item para labels legíveis em português.
     */
    private static array $typeLabels = [
        'evento'    => 'Evento',
        'curso'     => 'Curso',
        'workshop'  => 'Workshop',
    ];

    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'type'                => $this->type,
            'type_label'          => self::$typeLabels[$this->type] ?? $this->type,
            'title'               => $this->title,
            'slug'                => $this->slug,
            'description'         => $this->description,
            'short_description'   => $this->short_description,
            'featured_image_url'  => $this->featured_image_url,
            'starts_at'           => $this->starts_at?->toIso8601String(),
            'ends_at'             => $this->ends_at?->toIso8601String(),
            'location'            => $this->location,
            'slots'               => $this->slots,
            'slots_used'          => $this->slots_used,
            'slots_available'     => $this->slotsAvailable(),
            'price_centavos'      => $this->price_centavos,
            // Valor em reais para exibição no front-end
            'price_reais'         => round($this->price_centavos / 100, 2),
            'is_free'             => $this->isFree(),
            'status'              => $this->status,
            // Dados do seller — apenas quando há seller associado
            'seller'              => $this->seller ? [
                'id'   => $this->seller->id,
                'name' => $this->seller->name,
                'slug' => $this->seller->slug,
            ] : null,
        ];
    }
}
