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
