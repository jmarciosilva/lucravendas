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
