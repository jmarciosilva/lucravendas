<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Redis;

/**
 * Widget do dashboard com métricas das filas monitoradas pelo Laravel Horizon.
 */
class HorizonStatsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected function getStats(): array
    {
        // Horizon não roda em Windows (requer ext-pcntl) — exibe aviso amigável
        if (! extension_loaded('pcntl')) {
            return [
                Stat::make('Filas (Horizon)', 'N/D')
                    ->description('Horizon requer ambiente Linux/Docker')
                    ->color('gray'),
            ];
        }

        try {
            $prefix   = config('horizon.prefix', 'laravel_horizon:');
            $pending  = $this->queueSize('default') + $this->queueSize('high') + $this->queueSize('low');
            $failed   = (int) Redis::connection('default')->command('llen', ["{$prefix}failed_jobs"]);
            $processed = $this->recentProcessed($prefix);

            return [
                Stat::make('Jobs Pendentes', $pending)
                    ->description('Nas filas high + default + low')
                    ->color($pending > 100 ? 'danger' : ($pending > 10 ? 'warning' : 'success')),

                Stat::make('Processados (1h)', $processed)
                    ->description('Jobs concluídos na última hora')
                    ->color('info'),

                Stat::make('Jobs com Falha', $failed)
                    ->description('Total acumulado no Horizon')
                    ->color($failed > 0 ? 'danger' : 'success'),
            ];
        } catch (\Throwable) {
            return [
                Stat::make('Filas (Horizon)', 'Offline')
                    ->description('Redis não disponível')
                    ->color('danger'),
            ];
        }
    }

    private function queueSize(string $queue): int
    {
        try {
            return (int) Redis::connection('default')->command('llen', ["queues:{$queue}"]);
        } catch (\Throwable) {
            return 0;
        }
    }

    private function recentProcessed(string $prefix): int
    {
        try {
            // Horizon armazena contadores de throughput por minuto em Redis
            $keys  = Redis::connection('default')->command('keys', ["{$prefix}throughput:*"]);
            $total = 0;

            foreach ($keys as $key) {
                $total += (int) Redis::connection('default')->command('get', [$key]);
            }

            return $total;
        } catch (\Throwable) {
            return 0;
        }
    }
}
