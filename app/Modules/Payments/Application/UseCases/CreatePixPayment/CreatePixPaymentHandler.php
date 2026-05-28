<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\UseCases\CreatePixPayment;

use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Payments\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Domain\Repositories\PaymentTransactionRepositoryInterface;
use App\Modules\Payments\Infrastructure\Models\PaymentTransactionModel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CreatePixPaymentHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface              $orderRepository,
        private readonly PaymentTransactionRepositoryInterface $transactionRepository,
        private readonly PaymentGatewayInterface               $gateway,
    ) {}

    public function handle(CreatePixPaymentCommand $command): PaymentTransactionModel
    {
        $order = $this->orderRepository->findById($command->orderId, $command->tenantId);

        if ($order === null || $order->userId() !== $command->userId) {
            throw new RuntimeException('Pedido não encontrado.');
        }

        if (! $order->paymentStatus()->value() === 'pending') {
            throw new RuntimeException('Este pedido já possui um pagamento processado.');
        }

        // Chama o MP fora da transação — falhas no gateway não corrompem o banco
        $result = $this->gateway->createPixPayment(
            orderId: $command->orderId,
            amountCentavos: $order->total()->centavos(),
            payerEmail: $command->payerEmail,
        );

        return DB::transaction(function () use ($command, $order, $result) {
            return $this->transactionRepository->create([
                'order_id'        => $command->orderId,
                'gateway'         => 'mercadopago',
                'external_id'     => $result['external_id'],
                'method'          => 'pix',
                'amount'          => $order->total()->centavos(),
                'status'          => $result['status'],
                'qr_code'         => $result['qr_code'],
                'qr_code_base64'  => $result['qr_code_base64'],
            ]);
        });
    }
}
