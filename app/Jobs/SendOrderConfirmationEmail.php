<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Modules\Orders\Domain\Events\OrderCreated;
use App\Modules\Orders\Infrastructure\Models\OrderModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly OrderCreated $event)
    {
        $this->onQueue('high'); // E-mails de confirmação têm prioridade alta
    }

    public function handle(): void
    {
        $order = OrderModel::with('user')->find($this->event->orderId);

        if ($order === null) {
            return;
        }

        // Em desenvolvimento (MAIL_MAILER=log), o e-mail é registrado no log.
        // Na Fase 9 (produção), substituir por Mailable real.
        Log::info('Confirmação de pedido', [
            'order_id' => $this->event->orderId,
            'user_id'  => $this->event->userId,
            'total'    => number_format($this->event->total / 100, 2, ',', '.'),
            'email'    => $order->user?->email,
        ]);
    }
}
