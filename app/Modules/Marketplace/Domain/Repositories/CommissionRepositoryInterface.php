<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Repositories;

use App\Modules\Marketplace\Domain\Entities\Commission;

interface CommissionRepositoryInterface
{
    public function save(Commission $commission): Commission;

    /** @param Commission[] $commissions */
    public function saveMany(array $commissions): void;

    /** Cancela todas as comissões pendentes dos itens de um pedido */
    public function cancelByOrderId(int $orderId): void;

    /**
     * Retorna comissões pendentes agrupadas por seller.
     * Retorna [seller_id => ['amount' => int, 'commission_ids' => int[]]]
     *
     * @return array<int, array{amount: int, commission_ids: int[]}>
     */
    public function getPendingGroupedBySeller(): array;

    /** Marca um conjunto de comissões como pagas */
    public function markAsPaid(array $commissionIds): void;

    /** Métricas do seller para o dashboard */
    public function getMetricsForSeller(int $sellerId): array;
}
