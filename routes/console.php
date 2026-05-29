<?php

use App\Jobs\ExpireAbandonedCarts;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Remove carrinhos abandonados há mais de 24h — executa a cada hora
Schedule::job(new ExpireAbandonedCarts)->hourly()->name('expire-abandoned-carts');

// Processa repasses financeiros para sellers — executa toda segunda-feira às 9h
Schedule::job(new \App\Jobs\ProcessPayoutJob)->weeklyOn(1, '9:00')->name('process-seller-payouts');

// Publica posts agendados com publish_at vencido — executa a cada hora
Schedule::job(new \App\Jobs\PublishScheduledPost)->hourly()->name('publish-scheduled-posts');

// Snapshot de métricas do Horizon a cada 5 minutos (necessário para gráficos históricos)
Schedule::command('horizon:snapshot')->everyFiveMinutes()->name('horizon-snapshot');

// Backup diário do banco de dados às 2h — armazenado no S3
Schedule::command('backup:run --only-db')->dailyAt('02:00')->name('daily-backup');

// Limpeza de backups antigos às 2h30 (mantém política de retenção configurada)
Schedule::command('backup:clean')->dailyAt('02:30')->name('backup-cleanup');

// Monitoramento às 9h — envia notificação se o último backup estiver ausente ou falho
Schedule::command('backup:monitor')->dailyAt('09:00')->name('backup-monitor');
