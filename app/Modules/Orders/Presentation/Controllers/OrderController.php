<?php

declare(strict_types=1);

namespace App\Modules\Orders\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orders\Application\UseCases\Checkout\CheckoutCommand;
use App\Modules\Orders\Application\UseCases\Checkout\CheckoutHandler;
use App\Modules\Orders\Application\UseCases\GetOrders\GetOrdersHandler;
use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Orders\Presentation\Requests\CheckoutRequest;
use App\Modules\Orders\Presentation\Resources\OrderResource;
use App\Modules\Shipping\Domain\ValueObjects\ShippingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Gerencia o checkout e o histórico de pedidos do usuário autenticado.
 */
final class OrderController extends Controller
{
    public function __construct(
        private readonly CheckoutHandler         $checkoutHandler,
        private readonly GetOrdersHandler        $getOrdersHandler,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            // Constrói o endereço de entrega se os dados foram fornecidos
            $shippingAddress = null;
            $recipientState  = $request->validated('recipient_state');
            if ($request->filled('recipient_name') && $request->filled('recipient_zipcode') && $recipientState) {
                $shippingAddress = new ShippingAddress(
                    recipientName: $request->validated('recipient_name'),
                    zipcode: $request->validated('recipient_zipcode'),
                    address: $request->validated('recipient_address', ''),
                    number: $request->validated('recipient_number', 's/n'),
                    complement: $request->validated('recipient_complement'),
                    city: $request->validated('recipient_city', ''),
                    state: strtoupper($recipientState),
                );
            }

            $order = $this->checkoutHandler->handle(new CheckoutCommand(
                tenantId:            $request->header('X-Tenant-ID', ''),
                sessionId:           $request->header('X-Cart-Session'),
                userId:              $request->user()->id,
                paymentMethod:       $request->validated('payment_method'),
                notes:               $request->validated('notes'),
                shippingOptionId:    $request->validated('shipping_option_id'),
                shippingAddress:     $shippingAddress,
                shippingServiceCode: $request->validated('shipping_service_code'),
            ));

            return response()->json(['data' => new OrderResource($order)], 201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao processar o pedido.'], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $orders = $this->getOrdersHandler->handle(
                $request->user()->id,
                $request->header('X-Tenant-ID', ''),
            );

            return response()->json(['data' => OrderResource::collection($orders)]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao listar pedidos.'], 500);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $order = $this->orderRepository->findById(
                $id,
                $request->header('X-Tenant-ID', ''),
            );

            if ($order === null || $order->userId() !== $request->user()->id) {
                return response()->json(['message' => 'Pedido não encontrado.'], 404);
            }

            return response()->json(['data' => new OrderResource($order)]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao buscar pedido.'], 500);
        }
    }
}
