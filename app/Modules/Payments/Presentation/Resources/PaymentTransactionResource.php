<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentation\Resources;

use App\Modules\Payments\Infrastructure\Models\PaymentTransactionModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PaymentTransactionModel */
class PaymentTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var PaymentTransactionModel $tx */
        $tx = $this->resource;

        return [
            'id'             => $tx->id,
            'order_id'       => $tx->order_id,
            'method'         => $tx->method,
            'status'         => $tx->status,
            'amount'         => number_format($tx->amount / 100, 2, ',', '.'),
            'installments'   => $tx->installments,
            'qr_code'        => $tx->qr_code,
            'qr_code_base64' => $tx->qr_code_base64,
            'ticket_url'     => $tx->ticket_url,
            'created_at'     => $tx->created_at?->format('d/m/Y H:i'),
        ];
    }
}
