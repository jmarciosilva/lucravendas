<?php

declare(strict_types=1);

namespace App\Modules\Payments\Domain\Repositories;

use App\Modules\Payments\Infrastructure\Models\PaymentTransactionModel;

interface PaymentTransactionRepositoryInterface
{
    public function create(array $data): PaymentTransactionModel;

    public function findByOrderId(int $orderId): ?PaymentTransactionModel;

    public function findByExternalId(string $externalId): ?PaymentTransactionModel;

    public function updateStatus(int $id, string $status, array $payload = []): void;
}
