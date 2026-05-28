<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\UseCases\CreatePixPayment;

final readonly class CreatePixPaymentCommand
{
    public function __construct(
        public int    $orderId,
        public int    $userId,
        public string $tenantId,
        public string $payerEmail,
    ) {}
}
