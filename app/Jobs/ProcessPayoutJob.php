<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Modules\Marketplace\Application\UseCases\ProcessPayout\ProcessPayoutHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job semanal que processa repasses financeiros para os sellers.
 *
 * Agrupa comissões pendentes por seller, cria payout e marca comissões como pagas.
 * Agendado via Schedule em routes/console.php (weekly).
 */
class ProcessPayoutJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(ProcessPayoutHandler $handler): void
    {
        $count = $handler->handle();

        Log::info("ProcessPayoutJob concluído: {$count} repasse(s) criado(s).");
    }
}
