<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent da tabela `payouts`.
 *
 * Registra cada repasse financeiro realizado para um seller.
 * Valores monetários em centavos.
 */
class PayoutModel extends Model
{
    protected $table = 'payouts';

    /** @var list<string> */
    protected $fillable = [
        'seller_id',
        'amount',
        'status',
        'paid_at',
        'gateway_response',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'amount'           => 'integer',
        'gateway_response' => 'array',
        'paid_at'          => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(SellerModel::class, 'seller_id');
    }
}
