<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\ApproveSeller;

final class ApproveSellerCommand
{
    public function __construct(
        public readonly int $sellerId,
    ) {}
}
