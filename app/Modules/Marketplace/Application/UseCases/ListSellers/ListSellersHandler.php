<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\ListSellers;

use App\Modules\Marketplace\Domain\Entities\Seller;
use App\Modules\Marketplace\Domain\Repositories\SellerRepositoryInterface;

final class ListSellersHandler
{
    public function __construct(
        private readonly SellerRepositoryInterface $sellerRepository,
    ) {}

    /** @return Seller[] */
    public function handle(string $tenantId): array
    {
        return $this->sellerRepository->findActiveByTenant($tenantId);
    }
}
