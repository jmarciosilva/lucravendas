<?php

declare(strict_types=1);

namespace App\Modules\Payments\Infrastructure\Models;

use App\Modules\Orders\Infrastructure\Models\OrderModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransactionModel extends Model
{
    protected $table = 'payment_transactions';

    protected $fillable = [
        'order_id', 'gateway', 'external_id', 'method', 'amount',
        'status', 'qr_code', 'qr_code_base64', 'ticket_url',
        'installments', 'payload',
    ];

    protected $casts = [
        'amount'       => 'integer',
        'installments' => 'integer',
        'payload'      => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
