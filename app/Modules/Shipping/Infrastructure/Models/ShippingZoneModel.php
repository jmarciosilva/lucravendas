<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZoneModel extends Model
{
    protected $table = 'shipping_zones';

    protected $fillable = [
        'tenant_id', 'name', 'states', 'is_active',
    ];

    protected $casts = [
        'states'    => 'array',
        'is_active' => 'boolean',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRateModel::class, 'zone_id');
    }
}
