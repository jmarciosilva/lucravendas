<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Repositories;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Marketplace\Domain\Entities\Commission;
use App\Modules\Marketplace\Domain\Repositories\CommissionRepositoryInterface;
use App\Modules\Marketplace\Domain\ValueObjects\CommissionStatus;
use App\Modules\Marketplace\Infrastructure\Models\CommissionModel;
use App\Modules\Orders\Infrastructure\Models\OrderItemModel;
use Illuminate\Support\Facades\DB;

final class EloquentCommissionRepository implements CommissionRepositoryInterface
{
    public function save(Commission $commission): Commission
    {
        $model = CommissionModel::create([
            'order_item_id'     => $commission->orderItemId(),
            'seller_id'         => $commission->sellerId(),
            'gross_amount'      => $commission->grossAmount()->centavos(),
            'commission_amount' => $commission->commissionAmount()->centavos(),
            'net_amount'        => $commission->netAmount()->centavos(),
            'status'            => $commission->status()->value(),
        ]);

        return $this->toDomain($model);
    }

    /** @param Commission[] $commissions */
    public function saveMany(array $commissions): void
    {
        $rows = array_map(fn (Commission $c) => [
            'order_item_id'     => $c->orderItemId(),
            'seller_id'         => $c->sellerId(),
            'gross_amount'      => $c->grossAmount()->centavos(),
            'commission_amount' => $c->commissionAmount()->centavos(),
            'net_amount'        => $c->netAmount()->centavos(),
            'status'            => $c->status()->value(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ], $commissions);

        CommissionModel::insert($rows);
    }

    public function cancelByOrderId(int $orderId): void
    {
        // Busca os IDs dos itens deste pedido e cancela as comissões pendentes
        $itemIds = OrderItemModel::where('order_id', $orderId)->pluck('id');

        CommissionModel::whereIn('order_item_id', $itemIds)
            ->where('status', CommissionStatus::PENDING)
            ->update(['status' => CommissionStatus::CANCELLED]);
    }

    /** @return array<int, array{amount: int, commission_ids: int[]}> */
    public function getPendingGroupedBySeller(): array
    {
        $rows = CommissionModel::where('status', CommissionStatus::PENDING)
            ->select('seller_id', DB::raw('SUM(net_amount) as total_net'), DB::raw('GROUP_CONCAT(id) as ids'))
            ->groupBy('seller_id')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row->seller_id] = [
                'amount'         => (int) $row->total_net,
                'commission_ids' => array_map('intval', explode(',', $row->ids)),
            ];
        }

        return $result;
    }

    public function markAsPaid(array $commissionIds): void
    {
        CommissionModel::whereIn('id', $commissionIds)
            ->update(['status' => CommissionStatus::PAID]);
    }

    /** @return array{total_gmv: int, pending_commission: int, paid_commission: int, total_orders: int} */
    public function getMetricsForSeller(int $sellerId): array
    {
        $row = CommissionModel::where('seller_id', $sellerId)
            ->select([
                DB::raw('COALESCE(SUM(gross_amount), 0) as total_gmv'),
                DB::raw('COALESCE(SUM(CASE WHEN status = "pending" THEN commission_amount ELSE 0 END), 0) as pending_commission'),
                DB::raw('COALESCE(SUM(CASE WHEN status = "paid" THEN commission_amount ELSE 0 END), 0) as paid_commission'),
                DB::raw('COUNT(DISTINCT order_item_id) as total_items'),
            ])
            ->first();

        // Conta pedidos distintos pelos order_items vinculados
        $totalOrders = (int) CommissionModel::where('seller_id', $sellerId)
            ->join('order_items', 'commissions.order_item_id', '=', 'order_items.id')
            ->distinct('order_items.order_id')
            ->count('order_items.order_id');

        return [
            'total_gmv'          => (int) ($row->total_gmv ?? 0),
            'pending_commission' => (int) ($row->pending_commission ?? 0),
            'paid_commission'    => (int) ($row->paid_commission ?? 0),
            'total_orders'       => $totalOrders,
        ];
    }

    private function toDomain(CommissionModel $model): Commission
    {
        return Commission::restore(
            id: $model->id,
            orderItemId: $model->order_item_id,
            sellerId: $model->seller_id,
            grossAmount: Money::fromCentavos($model->gross_amount),
            commissionAmount: Money::fromCentavos($model->commission_amount),
            netAmount: Money::fromCentavos($model->net_amount),
            status: CommissionStatus::from($model->status),
        );
    }
}
