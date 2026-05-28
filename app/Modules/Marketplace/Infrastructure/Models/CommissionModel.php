<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent da tabela `commissions`.
 *
 * Registra a divisão financeira de cada item de pedido entre a plataforma e o seller.
 * Valores monetários armazenados em centavos.
 */
class CommissionModel extends Model
{
    protected $table = 'commissions';

    /** @var list<string> */
    protected $fillable = [
        'order_item_id',
        'seller_id',
        'gross_amount',
        'commission_amount',
        'net_amount',
        'status',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'gross_amount'      => 'integer',
        'commission_amount' => 'integer',
        'net_amount'        => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(SellerModel::class, 'seller_id');
    }
}
