<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Widgets;

use App\Modules\Marketing\Infrastructure\Models\ScheduledPostModel;
use App\Modules\Marketing\Domain\ValueObjects\PostStatus;
use App\Modules\Orders\Infrastructure\Models\OrderModel;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget do dashboard do lojista com métricas da sua própria loja.
 */
class LojistaOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tenantId = auth()->user()?->tenant_id ?? '';

        $receitaMes = (int) OrderModel::where('tenant_id', $tenantId)
            ->where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $pedidosPendentes = OrderModel::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'confirmed', 'processing'])
            ->count();

        $estoqueBaixo = ProductModel::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('stock', '<', 5)
            ->count();

        $postsAgendados = ScheduledPostModel::where('tenant_id', $tenantId)
            ->where('status', PostStatus::PENDING)
            ->count();

        return [
            Stat::make('Receita do Mês', 'R$ ' . number_format($receitaMes / 100, 2, ',', '.'))
                ->description('Pagamentos confirmados em ' . now()->translatedFormat('F'))
                ->color('success'),

            Stat::make('Pedidos Pendentes', $pedidosPendentes)
                ->description('Aguardando processamento')
                ->color($pedidosPendentes > 0 ? 'warning' : 'gray'),

            Stat::make('Estoque Baixo', $estoqueBaixo)
                ->description('Produtos ativos com menos de 5 unidades')
                ->color($estoqueBaixo > 0 ? 'danger' : 'success'),

            Stat::make('Posts Agendados', $postsAgendados)
                ->description('Aguardando publicação')
                ->color($postsAgendados > 0 ? 'info' : 'gray'),
        ];
    }
}
