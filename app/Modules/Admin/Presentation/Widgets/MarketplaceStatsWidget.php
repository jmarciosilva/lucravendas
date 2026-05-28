<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Widgets;

use App\Modules\Marketplace\Infrastructure\Models\CommissionModel;
use App\Modules\Marketplace\Infrastructure\Models\SellerModel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

/**
 * Widget do dashboard admin com métricas gerais do marketplace.
 */
class MarketplaceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        // GMV total: soma do valor bruto de todas as comissões (exceto canceladas)
        $totalGmv = (int) CommissionModel::whereIn('status', ['pending', 'paid'])
            ->sum('gross_amount');

        // Comissão pendente de repasse
        $pendingCommission = (int) CommissionModel::where('status', 'pending')
            ->sum('commission_amount');

        // Sellers ativos na plataforma
        $activeSellers = SellerModel::where('status', 'active')->count();

        // Sellers aguardando aprovação
        $pendingSellers = SellerModel::where('status', 'pending')->count();

        return [
            Stat::make('GMV Total', 'R$ ' . number_format($totalGmv / 100, 2, ',', '.'))
                ->description('Valor bruto movimentado pelos sellers')
                ->color('success'),

            Stat::make('Comissão a Repassar', 'R$ ' . number_format($pendingCommission / 100, 2, ',', '.'))
                ->description('Repasses pendentes para sellers')
                ->color('warning'),

            Stat::make('Sellers Ativos', $activeSellers)
                ->description("{$pendingSellers} aguardando aprovação")
                ->color('info'),
        ];
    }
}
