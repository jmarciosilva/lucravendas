<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\UseCases\CreateCardPayment;

final readonly class CreateCardPaymentCommand
{
    public function __construct(
        public int    $orderId,
        public int    $userId,
        public string $tenantId,
        public string $cardToken,
        public int    $installments,
        public string $payerEmail,
    ) {}
}
