<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\UseCases\RefundPayment;

use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Orders\Infrastructure\Models\OrderStatusHistoryModel;
use App\Modules\Payments\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Domain\Repositories\PaymentTransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class RefundPaymentHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface              $orderRepository,
        private readonly PaymentTransactionRepositoryInterface $transactionRepository,
        private readonly PaymentGatewayInterface               $gateway,
    ) {}

    public function handle(int $orderId, string $tenantId): void
    {
        $order = $this->orderRepository->findById($orderId, $tenantId);

        if ($order === null) {
            throw new RuntimeException('Pedido não encontrado.');
        }

        $transaction = $this->transactionRepository->findByOrderId($orderId);

        if ($transaction === null || $transaction->status !== 'approved') {
            throw new RuntimeException('Não há pagamento aprovado para estornar neste pedido.');
        }

        $success = $this->gateway->refund($transaction->external_id);

        if (! $success) {
            throw new RuntimeException('Falha ao processar o estorno no Mercado Pago.');
        }

        DB::transaction(function () use ($transaction, $order) {
            $this->transactionRepository->updateStatus($transaction->id, 'refunded');
            $order->markAsRefunded();
            $this->orderRepository->update($order);

            OrderStatusHistoryModel::create([
                'order_id' => $order->id(),
                'status'   => 'refunded',
                'notes'    => 'Estorno solicitado via painel admin.',
            ]);
        });
    }
}
