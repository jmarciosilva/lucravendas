<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Widgets;

use App\Modules\Marketing\Domain\ValueObjects\PostStatus;
use App\Modules\Marketing\Infrastructure\Models\ScheduledPostModel;
use App\Modules\Marketing\Infrastructure\Models\SocialAccountModel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget do dashboard admin com métricas do módulo de marketing.
 */
class MarketingStatsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $totalAccounts = SocialAccountModel::where('is_active', true)->count();

        $publishedLast30d = ScheduledPostModel::where('status', PostStatus::PUBLISHED)
            ->where('published_at', '>=', now()->subDays(30))
            ->count();

        $pendingPosts = ScheduledPostModel::where('status', PostStatus::PENDING)->count();

        $failedPosts = ScheduledPostModel::where('status', PostStatus::FAILED)->count();

        return [
            Stat::make('Contas Conectadas', $totalAccounts)
                ->description('Instagram e Facebook ativos')
                ->color('info'),

            Stat::make('Posts Publicados (30d)', $publishedLast30d)
                ->description('Últimos 30 dias')
                ->color('success'),

            Stat::make('Posts Pendentes', $pendingPosts)
                ->description("{$failedPosts} com falha")
                ->color($pendingPosts > 0 ? 'warning' : 'gray'),
        ];
    }
}
