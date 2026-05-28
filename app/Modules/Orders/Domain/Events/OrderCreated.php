<?php

declare(strict_types=1);

namespace App\Modules\Orders\Domain\Events;

final class OrderCreated
{
    public function __construct(
        public readonly int    $orderId,
        public readonly int    $userId,
        public readonly string $tenantId,
        public readonly int    $total,
    ) {}
}
