<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\UseCases\CreateCardPayment;

use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Payments\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Domain\Repositories\PaymentTransactionRepositoryInterface;
use App\Modules\Payments\Infrastructure\Models\PaymentTransactionModel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CreateCardPaymentHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface              $orderRepository,
        private readonly PaymentTransactionRepositoryInterface $transactionRepository,
        private readonly PaymentGatewayInterface               $gateway,
    ) {}

    public function handle(CreateCardPaymentCommand $command): PaymentTransactionModel
    {
        $order = $this->orderRepository->findById($command->orderId, $command->tenantId);

        if ($order === null || $order->userId() !== $command->userId) {
            throw new RuntimeException('Pedido não encontrado.');
        }

        $result = $this->gateway->createCardPayment(
            orderId: $command->orderId,
            amountCentavos: $order->total()->centavos(),
            cardToken: $command->cardToken,
            installments: $command->installments,
            payerEmail: $command->payerEmail,
        );

        return DB::transaction(function () use ($command, $order, $result) {
            $transaction = $this->transactionRepository->create([
                'order_id'     => $command->orderId,
                'gateway'      => 'mercadopago',
                'external_id'  => $result['external_id'],
                'method'       => 'credit_card',
                'amount'       => $order->total()->centavos(),
                'status'       => $result['status'],
                'installments' => $command->installments,
            ]);

            // Se aprovado imediatamente (comum em sandbox), atualiza o pedido
            if ($result['status'] === 'approved') {
                $order->markAsPaid();
                $this->orderRepository->update($order);
            }

            return $transaction;
        });
    }
}
