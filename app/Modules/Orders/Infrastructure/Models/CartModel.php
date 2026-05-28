<?php

declare(strict_types=1);

namespace App\Modules\Orders\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CartModel extends Model
{
    protected $table = 'carts';

    protected $fillable = [
        'tenant_id', 'session_id', 'user_id', 'coupon_id',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CartItemModel::class, 'cart_id')->with('product');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(CouponModel::class, 'coupon_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
