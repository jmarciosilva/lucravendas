<?php

declare(strict_types=1);

namespace App\Modules\Agenda\Infrastructure\Models;

use App\Modules\Marketplace\Infrastructure\Models\SellerModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Eloquent da tabela `agenda_items`.
 *
 * Representa eventos, cursos e workshops publicados na vitrine da loja.
 * Suporta controle de vagas, preço (ou gratuito) e multi-tenancy por tenant_id.
 */
class AgendaItemModel extends Model
{
    use SoftDeletes;

    protected $table = 'agenda_items';

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'seller_id',
        'type',
        'title',
        'slug',
        'description',
        'short_description',
        'featured_image_url',
        'starts_at',
        'ends_at',
        'location',
        'slots',
        'slots_used',
        'price_centavos',
        'status',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'price_centavos' => 'integer',
        'slots'          => 'integer',
        'slots_used'     => 'integer',
    ];

    /**
     * Verifica se o evento é gratuito (preço zero).
     */
    public function isFree(): bool
    {
        return $this->price_centavos === 0;
    }

    /**
     * Verifica se o evento tem limite de vagas (slots não nulo).
     */
    public function hasSlots(): bool
    {
        return $this->slots !== null;
    }

    /**
     * Retorna o número de vagas disponíveis.
     * Null indica vagas ilimitadas.
     */
    public function slotsAvailable(): ?int
    {
        if (! $this->hasSlots()) {
            return null;
        }

        return max(0, $this->slots - $this->slots_used);
    }

    /**
     * Verifica se o evento está disponível para inscrição.
     * Deve estar publicado e ter vagas (se limitado).
     */
    public function isAvailable(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->hasSlots() && $this->slots_used >= $this->slots) {
            return false;
        }

        return true;
    }

    /** Seller vinculado ao item (opcional) */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(SellerModel::class, 'seller_id');
    }

    /** Inscrições realizadas neste item */
    public function registrations(): HasMany
    {
        return $this->hasMany(AgendaRegistrationModel::class, 'agenda_item_id');
    }
}
