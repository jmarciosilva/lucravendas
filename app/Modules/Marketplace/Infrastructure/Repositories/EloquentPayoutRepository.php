<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Repositories;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Marketplace\Domain\Entities\Payout;
use App\Modules\Marketplace\Domain\Repositories\PayoutRepositoryInterface;
use App\Modules\Marketplace\Domain\ValueObjects\PayoutStatus;
use App\Modules\Marketplace\Infrastructure\Models\PayoutModel;

final class EloquentPayoutRepository implements PayoutRepositoryInterface
{
    public function save(Payout $payout): Payout
    {
        $model = PayoutModel::create([
            'seller_id'        => $payout->sellerId(),
            'amount'           => $payout->amount()->centavos(),
            'status'           => $payout->status()->value(),
            'paid_at'          => $payout->paidAt(),
            'gateway_response' => $payout->gatewayResponse(),
        ]);

        return $this->toDomain($model);
    }

    /** @return Payout[] */
    public function findBySeller(int $sellerId): array
    {
        return PayoutModel::where('seller_id', $sellerId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (PayoutModel $m) => $this->toDomain($m))
            ->all();
    }

    private function toDomain(PayoutModel $model): Payout
    {
        return Payout::restore(
            id: $model->id,
            sellerId: $model->seller_id,
            amount: Money::fromCentavos($model->amount),
            status: PayoutStatus::from($model->status),
            paidAt: $model->paid_at?->toIso8601String(),
            gatewayResponse: $model->gateway_response,
        );
    }
}
