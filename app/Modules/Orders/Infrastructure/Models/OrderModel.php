<?php

declare(strict_types=1);

namespace App\Modules\Orders\Infrastructure\Models;

use App\Models\User;
use App\Modules\Payments\Infrastructure\Models\PaymentTransactionModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderModel extends Model
{
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'tenant_id', 'user_id', 'status', 'subtotal', 'discount',
        'shipping_cost', 'total', 'payment_method', 'payment_status',
        'coupon_id', 'notes',
        // Endereço de entrega
        'recipient_name', 'recipient_zipcode', 'recipient_address',
        'recipient_number', 'recipient_complement', 'recipient_city', 'recipient_state',
        // Frete e rastreio
        'shipping_service_code', 'tracking_code', 'tracking_status', 'shipping_label_url',
    ];

    protected $casts = [
        'subtotal'      => 'integer',
        'discount'      => 'integer',
        'shipping_cost' => 'integer',
        'total'         => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistoryModel::class, 'order_id')->orderBy('created_at');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(CouponModel::class, 'coupon_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransactionModel::class, 'order_id');
    }
}
