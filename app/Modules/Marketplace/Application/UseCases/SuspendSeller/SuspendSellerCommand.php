<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\SuspendSeller;

final class SuspendSellerCommand
{
    public function __construct(
        public readonly int $sellerId,
    ) {}
}
