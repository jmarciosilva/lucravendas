<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shipping\Application\UseCases\CalculateShipping\CalculateShippingCommand;
use App\Modules\Shipping\Application\UseCases\CalculateShipping\CalculateShippingHandler;
use App\Modules\Shipping\Presentation\Requests\CalculateShippingRequest;
use App\Modules\Shipping\Presentation\Resources\ShippingOptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use RuntimeException;

final class ShippingController extends Controller
{
    public function __construct(
        private readonly CalculateShippingHandler $calculateHandler,
    ) {}

    /**
     * Calcula as opções de frete disponíveis para o CEP e carrinho atual.
     *
     * GET /api/v1/shipping/calculate?zipcode=01310-100&state=SP
     */
    public function calculate(CalculateShippingRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');
            $userId   = $request->user()?->id;
            $session  = $request->header('X-Cart-Session');

            // Subtotal atual para verificar threshold de frete grátis
            // Passamos 0 e o handler usa o carrinho para recalcular
            $options = $this->calculateHandler->handle(new CalculateShippingCommand(
                tenantId: $tenantId,
                toZipcode: $request->validated('zipcode'),
                toState: strtoupper($request->validated('state')),
                sessionId: $session,
                userId: $userId,
                subtotalCentavos: 0, // será calculado a partir do carrinho no handler
            ));

            return ShippingOptionResource::collection(collect($options));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao calcular frete.'], 500);
        }
    }
}
