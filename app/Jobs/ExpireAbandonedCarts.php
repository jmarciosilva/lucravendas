<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Modules\Orders\Infrastructure\Models\CartItemModel;
use App\Modules\Orders\Infrastructure\Models\CartModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Remove carrinhos abandonados há mais de 24 horas.
 * Registrado no scheduler — executa a cada hora via `php artisan schedule:run`.
 */
class ExpireAbandonedCarts implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('low'); // Limpeza periódica não é urgente
    }

    public function handle(): void
    {
        $cutoff = Carbon::now()->subHours(24);

        $ids = CartModel::where('updated_at', '<', $cutoff)->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        CartItemModel::whereIn('cart_id', $ids)->delete();
        CartModel::whereIn('id', $ids)->delete();

        Log::info("Carrinhos abandonados removidos: {$ids->count()}");
    }
}
