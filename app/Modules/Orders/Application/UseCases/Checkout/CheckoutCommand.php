<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\Checkout;

use App\Modules\Shipping\Domain\ValueObjects\ShippingAddress;

final readonly class CheckoutCommand
{
    public function __construct(
        public string          $tenantId,
        public ?string         $sessionId,
        public int             $userId,
        public ?string         $paymentMethod,
        public ?string         $notes,
        // Dados de frete — opcionais para pedidos sem entrega física
        public ?string         $shippingOptionId    = null,  // "internal_5" ou "me_2"
        public ?ShippingAddress $shippingAddress     = null,
        public ?string         $shippingServiceCode  = null,
    ) {}
}
