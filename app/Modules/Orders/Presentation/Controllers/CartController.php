<?php

declare(strict_types=1);

namespace App\Modules\Orders\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orders\Application\UseCases\AddCartItem\AddCartItemCommand;
use App\Modules\Orders\Application\UseCases\AddCartItem\AddCartItemHandler;
use App\Modules\Orders\Application\UseCases\ApplyCoupon\ApplyCouponCommand;
use App\Modules\Orders\Application\UseCases\ApplyCoupon\ApplyCouponHandler;
use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Orders\Application\UseCases\RemoveCartItem\RemoveCartItemHandler;
use App\Modules\Orders\Application\UseCases\RemoveCoupon\RemoveCouponHandler;
use App\Modules\Orders\Application\UseCases\UpdateCartItem\UpdateCartItemCommand;
use App\Modules\Orders\Application\UseCases\UpdateCartItem\UpdateCartItemHandler;
use App\Modules\Orders\Presentation\Requests\AddCartItemRequest;
use App\Modules\Orders\Presentation\Requests\ApplyCouponRequest;
use App\Modules\Orders\Presentation\Requests\UpdateCartItemRequest;
use App\Modules\Orders\Presentation\Resources\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Gerencia o carrinho de compras.
 * Suporta carrinhos anônimos (X-Cart-Session) e autenticados.
 */
final class CartController extends Controller
{
    public function __construct(
        private readonly GetCartHandler       $getCartHandler,
        private readonly AddCartItemHandler   $addItemHandler,
        private readonly UpdateCartItemHandler $updateItemHandler,
        private readonly RemoveCartItemHandler $removeItemHandler,
        private readonly ApplyCouponHandler   $applyCouponHandler,
        private readonly RemoveCouponHandler  $removeCouponHandler,
    ) {}

    public function show(Request $request): JsonResponse
    {
        try {
            ['session_id' => $sessionId, 'user_id' => $userId, 'tenant_id' => $tenantId]
                = $this->resolveCartIdentity($request);

            $cart = $this->getCartHandler->handle($tenantId, $sessionId, $userId);

            return response()->json(['data' => new CartResource($cart)]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao carregar o carrinho.'], 500);
        }
    }

    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        try {
            ['session_id' => $sessionId, 'user_id' => $userId, 'tenant_id' => $tenantId]
                = $this->resolveCartIdentity($request);

            $cart = $this->addItemHandler->handle(new AddCartItemCommand(
                tenantId:  $tenantId,
                sessionId: $sessionId,
                userId:    $userId,
                productId: $request->validated('product_id'),
                variantId: $request->validated('variant_id'),
                quantity:  (int) $request->validated('quantity'),
            ));

            return response()->json(['data' => new CartResource($cart)], 201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao adicionar item ao carrinho.'], 500);
        }
    }

    public function updateItem(UpdateCartItemRequest $request, int $id): JsonResponse
    {
        try {
            ['session_id' => $sessionId, 'user_id' => $userId, 'tenant_id' => $tenantId]
                = $this->resolveCartIdentity($request);

            $cart = $this->updateItemHandler->handle(new UpdateCartItemCommand(
                tenantId:  $tenantId,
                sessionId: $sessionId,
                userId:    $userId,
                itemId:    $id,
                quantity:  (int) $request->validated('quantity'),
            ));

            return response()->json(['data' => new CartResource($cart)]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao atualizar item do carrinho.'], 500);
        }
    }

    public function removeItem(Request $request, int $id): JsonResponse
    {
        try {
            ['session_id' => $sessionId, 'user_id' => $userId, 'tenant_id' => $tenantId]
                = $this->resolveCartIdentity($request);

            $cart = $this->removeItemHandler->handle($tenantId, $sessionId, $userId, $id);

            return response()->json(['data' => new CartResource($cart)]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao remover item do carrinho.'], 500);
        }
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        try {
            ['session_id' => $sessionId, 'user_id' => $userId, 'tenant_id' => $tenantId]
                = $this->resolveCartIdentity($request);

            $cart = $this->applyCouponHandler->handle(new ApplyCouponCommand(
                tenantId:   $tenantId,
                sessionId:  $sessionId,
                userId:     $userId,
                couponCode: $request->validated('code'),
            ));

            return response()->json(['data' => new CartResource($cart)]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao aplicar cupom.'], 500);
        }
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        try {
            ['session_id' => $sessionId, 'user_id' => $userId, 'tenant_id' => $tenantId]
                = $this->resolveCartIdentity($request);

            $cart = $this->removeCouponHandler->handle($tenantId, $sessionId, $userId);

            return response()->json(['data' => new CartResource($cart)]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao remover cupom.'], 500);
        }
    }

    private function resolveCartIdentity(Request $request): array
    {
        return [
            'session_id' => $request->header('X-Cart-Session'),
            'user_id'    => $request->user()?->id,
            'tenant_id'  => $request->header('X-Tenant-ID', ''),
        ];
    }
}
