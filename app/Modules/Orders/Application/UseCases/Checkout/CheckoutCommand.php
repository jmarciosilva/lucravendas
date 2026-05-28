<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\Checkout;

final readonly class CheckoutCommand
{
    public function __construct(
        public string  $tenantId,
        public ?string $sessionId,
        public int     $userId,
        public ?string $paymentMethod,
        public ?string $notes,
    ) {}
}
