<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\UseCases\CreateBoletoPayment;

final readonly class CreateBoletoPaymentCommand
{
    public function __construct(
        public int    $orderId,
        public int    $userId,
        public string $tenantId,
        public string $payerEmail,
        public string $payerCpf,
    ) {}
}
