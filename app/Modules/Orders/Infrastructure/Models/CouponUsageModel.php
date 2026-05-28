<?php

declare(strict_types=1);

namespace App\Modules\Orders\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsageModel extends Model
{
    protected $table = 'coupon_usages';

    public $timestamps = false;

    protected $fillable = [
        'coupon_id', 'user_id', 'order_id', 'discount_amount',
    ];

    protected $casts = [
        'discount_amount' => 'integer',
        'created_at'      => 'datetime',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(CouponModel::class, 'coupon_id');
    }
}
