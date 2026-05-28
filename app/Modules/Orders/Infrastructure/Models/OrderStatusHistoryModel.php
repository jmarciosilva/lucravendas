<?php

declare(strict_types=1);

namespace App\Modules\Orders\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistoryModel extends Model
{
    protected $table = 'order_status_history';

    public $timestamps = false;

    protected $fillable = ['order_id', 'status', 'notes'];

    protected $casts = ['created_at' => 'datetime'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
