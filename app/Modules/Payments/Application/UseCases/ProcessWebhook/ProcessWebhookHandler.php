<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\UseCases\ProcessWebhook;

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Orders\Infrastructure\Models\OrderItemModel;
use App\Modules\Orders\Infrastructure\Models\OrderStatusHistoryModel;
use App\Modules\Payments\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Domain\Events\PaymentApproved;
use App\Modules\Payments\Domain\Events\PaymentRejected;
use App\Modules\Payments\Domain\Repositories\PaymentTransactionRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Processa notificações IPN/webhook do Mercado Pago.
 *
 * Valida assinatura → consulta status real → atualiza transaction e pedido.
 * Retorna true se processado com sucesso, false se já processado (idempotente).
 */
final class ProcessWebhookHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface              $orderRepository,
        private readonly PaymentTransactionRepositoryInterface $transactionRepository,
        private readonly PaymentGatewayInterface               $gateway,
        private readonly Dispatcher                            $events,
    ) {}

    public function handle(string $signature, string $requestId, array $payload): bool
    {
        $paymentId = $payload['data']['id'] ?? null;

        if (! $paymentId) {
            return false;
        }

        $this->validateSignature($signature, $requestId, (string) $paymentId, $payload['date_created'] ?? '');

        // Consulta o status real no MP (não confia apenas no payload do webhook)
        $status = $this->gateway->getPaymentStatus((string) $paymentId);

        $transaction = $this->transactionRepository->findByExternalId((string) $paymentId);

        if ($transaction === null) {
            return false;
        }

        // Idempotência: ignora se o status já está atualizado
        if ($transaction->status === $status) {
            return true;
        }

        $order = $this->orderRepository->findByIdRaw($transaction->order_id);

        if ($order === null) {
            return false;
        }

        DB::transaction(function () use ($transaction, $order, $status, $payload) {
            $this->transactionRepository->updateStatus($transaction->id, $status, $payload);

            if ($status === 'approved') {
                $order->markAsPaid();
                $this->orderRepository->update($order);

                OrderStatusHistoryModel::create([
                    'order_id' => $order->id(),
                    'status'   => $order->status()->value(),
                    'notes'    => 'Pagamento confirmado via Mercado Pago.',
                ]);

                $this->events->dispatch(new PaymentApproved(
                    orderId: $order->id(),
                    transactionId: $transaction->id,
                    amountCentavos: $transaction->amount,
                    method: $transaction->method,
                ));
            }

            if (in_array($status, ['rejected', 'cancelled'], true)) {
                $order->markAsPaymentFailed();
                $this->orderRepository->update($order);

                // Restaura o estoque dos itens do pedido
                $items = OrderItemModel::where('order_id', $order->id())->get();
                foreach ($items as $item) {
                    ProductModel::where('id', $item->product_id)
                        ->increment('stock', $item->quantity);
                }

                OrderStatusHistoryModel::create([
                    'order_id' => $order->id(),
                    'status'   => $order->status()->value(),
                    'notes'    => 'Pagamento recusado. Pedido cancelado e estoque restaurado.',
                ]);

                $this->events->dispatch(new PaymentRejected(
                    orderId: $order->id(),
                    transactionId: $transaction->id,
                    reason: $status,
                ));
            }

            if ($status === 'refunded') {
                $order->markAsRefunded();
                $this->orderRepository->update($order);

                OrderStatusHistoryModel::create([
                    'order_id' => $order->id(),
                    'status'   => 'refunded',
                    'notes'    => 'Pagamento estornado.',
                ]);
            }
        });

        return true;
    }

    /**
     * Valida o header x-signature enviado pelo Mercado Pago.
     * Formato: "ts=TIMESTAMP,v1=HASH"
     * Hash = HMAC-SHA256("id=<id>;request-id=<rid>;ts=<ts>", webhook_secret)
     */
    private function validateSignature(
        string $signature,
        string $requestId,
        string $paymentId,
        string $ts,
    ): void {
        $secret = config('payments.mercadopago.webhook_secret');

        // Em ambiente de teste sem secret configurado, pula a validação
        if (empty($secret)) {
            return;
        }

        // Extrai ts e v1 do header
        preg_match('/ts=([^,]+)/', $signature, $tsMatch);
        preg_match('/v1=([^,]+)/', $signature, $hashMatch);

        $receivedTs   = $tsMatch[1] ?? '';
        $receivedHash = $hashMatch[1] ?? '';

        $manifest = "id={$paymentId};request-id={$requestId};ts={$receivedTs}";
        $expected = hash_hmac('sha256', $manifest, $secret);

        if (! hash_equals($expected, $receivedHash)) {
            throw new RuntimeException('Assinatura do webhook inválida.', 401);
        }
    }
}
