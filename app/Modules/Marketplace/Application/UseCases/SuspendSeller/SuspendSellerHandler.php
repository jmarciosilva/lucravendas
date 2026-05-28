<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\SuspendSeller;

use App\Modules\Marketplace\Domain\Entities\Seller;
use App\Modules\Marketplace\Domain\Repositories\SellerRepositoryInterface;
use RuntimeException;

final class SuspendSellerHandler
{
    public function __construct(
        private readonly SellerRepositoryInterface $sellerRepository,
    ) {}

    public function handle(SuspendSellerCommand $command): Seller
    {
        $seller = $this->sellerRepository->findById($command->sellerId);

        if ($seller === null) {
            throw new RuntimeException('Seller não encontrado.');
        }

        $seller->suspend();
        $this->sellerRepository->update($seller);

        return $seller;
    }
}
