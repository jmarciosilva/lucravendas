<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\UseCases\CreateBoletoPayment;

use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Payments\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Domain\Repositories\PaymentTransactionRepositoryInterface;
use App\Modules\Payments\Infrastructure\Models\PaymentTransactionModel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CreateBoletoPaymentHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface              $orderRepository,
        private readonly PaymentTransactionRepositoryInterface $transactionRepository,
        private readonly PaymentGatewayInterface               $gateway,
    ) {}

    public function handle(CreateBoletoPaymentCommand $command): PaymentTransactionModel
    {
        $order = $this->orderRepository->findById($command->orderId, $command->tenantId);

        if ($order === null || $order->userId() !== $command->userId) {
            throw new RuntimeException('Pedido não encontrado.');
        }

        $result = $this->gateway->createBoletoPayment(
            orderId: $command->orderId,
            amountCentavos: $order->total()->centavos(),
            payerEmail: $command->payerEmail,
            payerCpf: $command->payerCpf,
        );

        return DB::transaction(function () use ($command, $order, $result) {
            return $this->transactionRepository->create([
                'order_id'   => $command->orderId,
                'gateway'    => 'mercadopago',
                'external_id'=> $result['external_id'],
                'method'     => 'boleto',
                'amount'     => $order->total()->centavos(),
                'status'     => $result['status'],
                'ticket_url' => $result['ticket_url'],
            ]);
        });
    }
}
