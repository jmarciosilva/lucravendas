<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Application\UseCases\ProcessTrackingWebhook;

use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Orders\Domain\ValueObjects\OrderStatus;
use App\Modules\Orders\Infrastructure\Models\OrderStatusHistoryModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Processa webhooks de rastreio enviados pelo Melhor Envio.
 *
 * Atualiza tracking_status no pedido e, se entregue, transiciona para 'delivered'.
 */
final class ProcessTrackingWebhookHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function handle(array $payload): bool
    {
        $trackingCode = $payload['tracking'] ?? null;
        $statusEvent  = $payload['status']['event'] ?? $payload['event'] ?? null;

        if (! $trackingCode || ! $statusEvent) {
            Log::warning('TrackingWebhook: payload inválido.', $payload);
            return false;
        }

        $order = $this->orderRepository->findByTrackingCode($trackingCode);

        if ($order === null) {
            Log::info("TrackingWebhook: pedido não encontrado para tracking {$trackingCode}.");
            return false;
        }

        // Mapa de status do ME para descrição pt-BR
        $statusLabels = [
            'posted'           => 'Postado',
            'in_transit'       => 'Em trânsito',
            'out_for_delivery' => 'Saiu para entrega',
            'delivered'        => 'Entregue',
            'undelivered'      => 'Tentativa de entrega falhou',
            'waiting_pickup'   => 'Aguardando retirada',
        ];

        DB::transaction(function () use ($order, $trackingCode, $statusEvent, $statusLabels) {
            $this->orderRepository->updateTracking(
                orderId: $order->id(),
                trackingCode: $trackingCode,
                trackingStatus: $statusEvent,
                labelUrl: null, // não altera a URL existente
            );

            // Se entregue, transiciona o status do pedido
            if ($statusEvent === 'delivered') {
                $order->markAsDelivered();
                $this->orderRepository->update($order);
            }

            $label = $statusLabels[$statusEvent] ?? $statusEvent;

            OrderStatusHistoryModel::create([
                'order_id' => $order->id(),
                'status'   => $order->status()->value(),
                'notes'    => "Rastreio: {$label} ({$trackingCode})",
            ]);
        });

        return true;
    }
}
