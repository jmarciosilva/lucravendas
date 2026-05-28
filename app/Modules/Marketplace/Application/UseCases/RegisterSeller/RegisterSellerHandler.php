<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\RegisterSeller;

use App\Modules\Marketplace\Domain\Entities\Seller;
use App\Modules\Marketplace\Domain\Repositories\SellerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class RegisterSellerHandler
{
    // Taxa padrão de comissão para novos sellers (configurável futuramente por plano)
    private const DEFAULT_COMMISSION_RATE = 10.0;

    public function __construct(
        private readonly SellerRepositoryInterface $sellerRepository,
    ) {}

    public function handle(RegisterSellerCommand $command): Seller
    {
        return DB::transaction(function () use ($command) {
            // Impede cadastro duplicado de seller para o mesmo usuário no tenant
            $existing = $this->sellerRepository->findByUserId($command->userId, $command->tenantId);
            if ($existing !== null) {
                throw new RuntimeException('Este usuário já possui uma loja cadastrada neste tenant.');
            }

            if ($this->sellerRepository->slugExistsInTenant($command->slug, $command->tenantId)) {
                throw new RuntimeException("O slug '{$command->slug}' já está em uso neste tenant.");
            }

            $seller = Seller::create(
                name: $command->name,
                slug: $command->slug,
                tenantId: $command->tenantId,
                userId: $command->userId,
                commissionRate: self::DEFAULT_COMMISSION_RATE,
                bankInfo: $command->bankInfo,
                description: $command->description,
            );

            return $this->sellerRepository->save($seller);
        });
    }
}
