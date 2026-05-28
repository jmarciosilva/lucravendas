<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Application\UseCases\ProcessWebhook\ProcessWebhookHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Recebe notificações IPN/webhook do Mercado Pago.
 * Não requer autenticação — chamado pelos servidores do MP.
 */
final class WebhookController extends Controller
{
    public function __construct(
        private readonly ProcessWebhookHandler $handler,
    ) {}

    public function mercadopago(Request $request): JsonResponse
    {
        try {
            $this->handler->handle(
                signature:  $request->header('x-signature', ''),
                requestId:  $request->header('x-request-id', ''),
                payload:    $request->all(),
            );

            // MP espera HTTP 200 para confirmar recebimento do webhook
            return response()->json(['received' => true]);
        } catch (\RuntimeException $e) {
            // Assinatura inválida tem código 401 setado na exceção
            $status = $e->getCode() === 401 ? 401 : 422;

            return response()->json(['message' => $e->getMessage()], $status);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao processar webhook.'], 500);
        }
    }
}
