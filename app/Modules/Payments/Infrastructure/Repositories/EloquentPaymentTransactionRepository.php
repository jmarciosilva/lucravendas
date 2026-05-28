<?php

declare(strict_types=1);

namespace App\Modules\Payments\Infrastructure\Repositories;

use App\Modules\Payments\Domain\Repositories\PaymentTransactionRepositoryInterface;
use App\Modules\Payments\Infrastructure\Models\PaymentTransactionModel;

class EloquentPaymentTransactionRepository implements PaymentTransactionRepositoryInterface
{
    public function create(array $data): PaymentTransactionModel
    {
        return PaymentTransactionModel::create($data);
    }

    public function findByOrderId(int $orderId): ?PaymentTransactionModel
    {
        return PaymentTransactionModel::where('order_id', $orderId)->latest()->first();
    }

    public function findByExternalId(string $externalId): ?PaymentTransactionModel
    {
        return PaymentTransactionModel::where('external_id', $externalId)->first();
    }

    public function updateStatus(int $id, string $status, array $payload = []): void
    {
        $data = ['status' => $status];

        if (! empty($payload)) {
            $data['payload'] = $payload;
        }

        PaymentTransactionModel::where('id', $id)->update($data);
    }
}
