<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shipping\Application\UseCases\ProcessTrackingWebhook\ProcessTrackingWebhookHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Recebe webhooks de rastreio do Melhor Envio.
 *
 * Endpoint sem autenticação — chamado diretamente pelo ME.
 * POST /api/v1/webhooks/shipping
 */
final class TrackingWebhookController extends Controller
{
    public function __construct(
        private readonly ProcessTrackingWebhookHandler $handler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $processed = $this->handler->handle($request->all());

        return response()->json(['ok' => $processed]);
    }
}
