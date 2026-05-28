<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\GetSellerDashboard;

use App\Modules\Marketplace\Domain\Repositories\CommissionRepositoryInterface;
use App\Modules\Marketplace\Domain\Repositories\SellerRepositoryInterface;
use RuntimeException;

final class GetSellerDashboardHandler
{
    public function __construct(
        private readonly SellerRepositoryInterface    $sellerRepository,
        private readonly CommissionRepositoryInterface $commissionRepository,
    ) {}

    /**
     * Retorna dados do dashboard do seller autenticado.
     *
     * @return array{seller: array, metrics: array}
     */
    public function handle(int $userId, string $tenantId): array
    {
        $seller = $this->sellerRepository->findByUserId($userId, $tenantId);

        if ($seller === null) {
            throw new RuntimeException('Nenhuma loja encontrada para este usuário.', 404);
        }

        $metrics = $this->commissionRepository->getMetricsForSeller($seller->id());

        return [
            'seller' => [
                'id'     => $seller->id(),
                'name'   => $seller->name(),
                'slug'   => $seller->slug(),
                'status' => $seller->status()->value(),
            ],
            'metrics' => [
                'total_orders'              => $metrics['total_orders'],
                'total_gmv_centavos'        => $metrics['total_gmv'],
                'pending_commission_centavos' => $metrics['pending_commission'],
                'paid_commission_centavos'  => $metrics['paid_commission'],
            ],
        ];
    }
}
