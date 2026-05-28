<?php

declare(strict_types=1);

namespace App\Modules\Orders\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemModel extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id', 'product_id', 'variant_id',
        'name', 'sku', 'unit_price', 'quantity', 'total',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'quantity'   => 'integer',
        'total'      => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
