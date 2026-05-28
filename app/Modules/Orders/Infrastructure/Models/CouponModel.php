<?php

declare(strict_types=1);

namespace App\Modules\Orders\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CouponModel extends Model
{
    protected $table = 'coupons';

    protected $fillable = [
        'tenant_id', 'code', 'type', 'value',
        'min_order_value', 'max_uses', 'uses_count',
        'expires_at', 'is_active',
    ];

    protected $casts = [
        'value'           => 'integer',
        'min_order_value' => 'integer',
        'max_uses'        => 'integer',
        'uses_count'      => 'integer',
        'is_active'       => 'boolean',
        'expires_at'      => 'datetime',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsageModel::class, 'coupon_id');
    }

    public function isValid(int $subtotalCentavos): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        if ($subtotalCentavos < $this->min_order_value) {
            return false;
        }

        return true;
    }
}
