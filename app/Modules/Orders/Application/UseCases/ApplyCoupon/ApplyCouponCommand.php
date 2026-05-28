<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\ApplyCoupon;

final readonly class ApplyCouponCommand
{
    public function __construct(
        public string  $tenantId,
        public ?string $sessionId,
        public ?int    $userId,
        public string  $couponCode,
    ) {}
}
