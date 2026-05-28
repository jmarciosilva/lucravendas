<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\ApproveSeller;

use App\Modules\Marketplace\Domain\Entities\Seller;
use App\Modules\Marketplace\Domain\Repositories\SellerRepositoryInterface;
use RuntimeException;

final class ApproveSellerHandler
{
    public function __construct(
        private readonly SellerRepositoryInterface $sellerRepository,
    ) {}

    public function handle(ApproveSellerCommand $command): Seller
    {
        $seller = $this->sellerRepository->findById($command->sellerId);

        if ($seller === null) {
            throw new RuntimeException('Seller não encontrado.');
        }

        $seller->approve();
        $this->sellerRepository->update($seller);

        return $seller;
    }
}
