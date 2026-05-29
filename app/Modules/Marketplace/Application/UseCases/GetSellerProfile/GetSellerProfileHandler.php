<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\GetSellerProfile;

use App\Modules\Marketplace\Domain\Entities\Seller;
use App\Modules\Marketplace\Domain\Repositories\SellerRepositoryInterface;
use App\Modules\Marketplace\Domain\ValueObjects\SellerStatus;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

final class GetSellerProfileHandler
{
    public function __construct(
        private readonly SellerRepositoryInterface $sellerRepository,
    ) {}

    public function handle(GetSellerProfileCommand $command): Seller
    {
        $seller = Cache::remember(
            CacheKeys::seller($command->tenantId, $command->slug),
            CacheKeys::SELLERS_TTL,
            fn () => $this->sellerRepository->findBySlug($command->slug, $command->tenantId)
        );

        if ($seller === null || ! $seller->status()->isActive()) {
            // Garante que perfis inativos não fiquem em cache
            Cache::forget(CacheKeys::seller($command->tenantId, $command->slug));
            throw new RuntimeException('Seller não encontrado ou inativo.', 404);
        }

        return $seller;
    }
}
