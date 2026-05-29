<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\ListSellers;

use App\Modules\Marketplace\Domain\Entities\Seller;
use App\Modules\Marketplace\Domain\Repositories\SellerRepositoryInterface;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

final class ListSellersHandler
{
    public function __construct(
        private readonly SellerRepositoryInterface $sellerRepository,
    ) {}

    /** @return Seller[] */
    public function handle(string $tenantId): array
    {
        return Cache::remember(
            CacheKeys::sellers($tenantId),
            CacheKeys::SELLERS_TTL,
            fn () => $this->sellerRepository->findActiveByTenant($tenantId)
        );
    }
}
