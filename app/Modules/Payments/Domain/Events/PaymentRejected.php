<?php

declare(strict_types=1);

namespace App\Modules\Payments\Domain\Events;

final class PaymentRejected
{
    public function __construct(
        public readonly int    $orderId,
        public readonly int    $transactionId,
        public readonly string $reason,
    ) {}
}
