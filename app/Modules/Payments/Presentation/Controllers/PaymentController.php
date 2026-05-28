<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Application\UseCases\CreateBoletoPayment\CreateBoletoPaymentCommand;
use App\Modules\Payments\Application\UseCases\CreateBoletoPayment\CreateBoletoPaymentHandler;
use App\Modules\Payments\Application\UseCases\CreateCardPayment\CreateCardPaymentCommand;
use App\Modules\Payments\Application\UseCases\CreateCardPayment\CreateCardPaymentHandler;
use App\Modules\Payments\Application\UseCases\CreatePixPayment\CreatePixPaymentCommand;
use App\Modules\Payments\Application\UseCases\CreatePixPayment\CreatePixPaymentHandler;
use App\Modules\Payments\Domain\Repositories\PaymentTransactionRepositoryInterface;
use App\Modules\Payments\Presentation\Requests\CreateBoletoPaymentRequest;
use App\Modules\Payments\Presentation\Requests\CreateCardPaymentRequest;
use App\Modules\Payments\Presentation\Requests\CreatePixPaymentRequest;
use App\Modules\Payments\Presentation\Resources\PaymentTransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Endpoints de pagamento via Mercado Pago.
 * Todos exigem autenticação (auth:sanctum).
 */
final class PaymentController extends Controller
{
    public function __construct(
        private readonly CreatePixPaymentHandler               $pixHandler,
        private readonly CreateCardPaymentHandler              $cardHandler,
        private readonly CreateBoletoPaymentHandler            $boletoHandler,
        private readonly PaymentTransactionRepositoryInterface $transactionRepository,
    ) {}

    public function pix(CreatePixPaymentRequest $request): JsonResponse
    {
        try {
            $transaction = $this->pixHandler->handle(new CreatePixPaymentCommand(
                orderId:    (int) $request->validated('order_id'),
                userId:     $request->user()->id,
                tenantId:   $request->header('X-Tenant-ID', ''),
                payerEmail: $request->user()->email,
            ));

            return response()->json(['data' => new PaymentTransactionResource($transaction)], 201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao gerar cobrança PIX.'], 500);
        }
    }

    public function card(CreateCardPaymentRequest $request): JsonResponse
    {
        try {
            $transaction = $this->cardHandler->handle(new CreateCardPaymentCommand(
                orderId:      (int) $request->validated('order_id'),
                userId:       $request->user()->id,
                tenantId:     $request->header('X-Tenant-ID', ''),
                cardToken:    $request->validated('card_token'),
                installments: (int) $request->validated('installments'),
                payerEmail:   $request->validated('payer_email'),
            ));

            return response()->json(['data' => new PaymentTransactionResource($transaction)], 201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao processar pagamento com cartão.'], 500);
        }
    }

    public function boleto(CreateBoletoPaymentRequest $request): JsonResponse
    {
        try {
            $transaction = $this->boletoHandler->handle(new CreateBoletoPaymentCommand(
                orderId:    (int) $request->validated('order_id'),
                userId:     $request->user()->id,
                tenantId:   $request->header('X-Tenant-ID', ''),
                payerEmail: $request->validated('payer_email'),
                payerCpf:   $request->validated('payer_cpf'),
            ));

            return response()->json(['data' => new PaymentTransactionResource($transaction)], 201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao gerar boleto bancário.'], 500);
        }
    }

    public function status(Request $request, int $orderId): JsonResponse
    {
        try {
            $transaction = $this->transactionRepository->findByOrderId($orderId);

            if ($transaction === null) {
                return response()->json(['message' => 'Nenhum pagamento encontrado para este pedido.'], 404);
            }

            return response()->json(['data' => new PaymentTransactionResource($transaction)]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao consultar status do pagamento.'], 500);
        }
    }
}
