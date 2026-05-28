<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRateModel extends Model
{
    protected $table = 'shipping_rates';

    protected $fillable = [
        'zone_id', 'tenant_id', 'name', 'carrier', 'service_code',
        'base_price', 'price_per_kg', 'min_days', 'max_days',
        'free_shipping_threshold', 'is_active',
    ];

    protected $casts = [
        'base_price'               => 'integer',
        'price_per_kg'             => 'integer',
        'min_days'                 => 'integer',
        'max_days'                 => 'integer',
        'free_shipping_threshold'  => 'integer',
        'is_active'                => 'boolean',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZoneModel::class, 'zone_id');
    }
}
