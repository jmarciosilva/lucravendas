<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Repositories;

use App\Modules\Marketplace\Domain\Entities\Payout;

interface PayoutRepositoryInterface
{
    public function save(Payout $payout): Payout;

    /** @return Payout[] */
    public function findBySeller(int $sellerId): array;
}
