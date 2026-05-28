<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\GetSellerProfile;

use App\Modules\Marketplace\Domain\Entities\Seller;
use App\Modules\Marketplace\Domain\Repositories\SellerRepositoryInterface;
use App\Modules\Marketplace\Domain\ValueObjects\SellerStatus;
use RuntimeException;

final class GetSellerProfileHandler
{
    public function __construct(
        private readonly SellerRepositoryInterface $sellerRepository,
    ) {}

    public function handle(GetSellerProfileCommand $command): Seller
    {
        $seller = $this->sellerRepository->findBySlug($command->slug, $command->tenantId);

        if ($seller === null || ! $seller->status()->isActive()) {
            throw new RuntimeException('Seller não encontrado ou inativo.', 404);
        }

        return $seller;
    }
}
