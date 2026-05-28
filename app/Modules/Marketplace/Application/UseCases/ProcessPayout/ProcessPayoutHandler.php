<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\ProcessPayout;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Marketplace\Domain\Entities\Payout;
use App\Modules\Marketplace\Domain\Repositories\CommissionRepositoryInterface;
use App\Modules\Marketplace\Domain\Repositories\PayoutRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Agrupa comissões pendentes por seller e cria registros de repasse (payout).
 *
 * Execução semanal via ProcessPayoutJob.
 * O split real via API do Mercado Pago Marketplace será integrado na Fase 9 —
 * por ora o payout fica em status 'pending' para processamento manual ou futuro gateway.
 */
final class ProcessPayoutHandler
{
    public function __construct(
        private readonly CommissionRepositoryInterface $commissionRepository,
        private readonly PayoutRepositoryInterface     $payoutRepository,
    ) {}

    /**
     * Processa todos os repasses pendentes.
     *
     * @return int Número de payouts criados
     */
    public function handle(): int
    {
        $grouped = $this->commissionRepository->getPendingGroupedBySeller();

        if (empty($grouped)) {
            return 0;
        }

        $count = 0;

        foreach ($grouped as $sellerId => $data) {
            DB::transaction(function () use ($sellerId, $data) {
                $payout = Payout::create(
                    sellerId: $sellerId,
                    amount: Money::fromCentavos($data['amount']),
                );

                $this->payoutRepository->save($payout);
                $this->commissionRepository->markAsPaid($data['commission_ids']);
            });

            Log::info("Payout criado para seller #{$sellerId}: R$ " . number_format($data['amount'] / 100, 2, ',', '.'));
            $count++;
        }

        return $count;
    }
}
