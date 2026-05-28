<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Widgets;

use App\Models\User;
use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget de estatísticas gerais da plataforma no dashboard admin.
 *
 * Mostra métricas consolidadas de todos os tenants para visão macro da LucraOne.
 */
final class PlatformStatsWidget extends StatsOverviewWidget
{
    /** @return list<Stat> */
    protected function getStats(): array
    {
        return [
            Stat::make('Lojas Ativas', TenantModel::where('status', 'active')->count())
                ->description('Tenants com status ativo')
                ->icon('heroicon-o-building-storefront')
                ->color('success'),

            Stat::make('Total de Usuários', User::count())
                ->description('Todos os usuários registrados')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Super Admins', User::role('super_admin')->count())
                ->description('Usuários LucraOne')
                ->icon('heroicon-o-shield-check')
                ->color('warning'),
        ];
    }
}
