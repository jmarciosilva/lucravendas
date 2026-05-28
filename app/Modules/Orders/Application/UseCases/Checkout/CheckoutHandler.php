<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\Checkout;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Marketplace\Application\UseCases\CalculateCommissions\CommissionCalculatorService;
use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Orders\Domain\Entities\Order;
use App\Modules\Orders\Domain\Entities\OrderItem;
use App\Modules\Orders\Domain\Repositories\CartRepositoryInterface;
use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Orders\Infrastructure\Models\CouponModel;
use App\Modules\Orders\Infrastructure\Models\CouponUsageModel;
use App\Modules\Shipping\Domain\Repositories\ShippingRateRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CheckoutHandler
{
    public function __construct(
        private readonly CartRepositoryInterface      $cartRepository,
        private readonly OrderRepositoryInterface     $orderRepository,
        private readonly GetCartHandler               $getCartHandler,
        private readonly CommissionCalculatorService  $commissionCalculator,
        private readonly ShippingRateRepositoryInterface $shippingRateRepository,
        private readonly Dispatcher                   $events,
    ) {}

    public function handle(CheckoutCommand $command): Order
    {
        $order = DB::transaction(function () use ($command) {
            $cart = $this->getCartHandler->handle(
                $command->tenantId,
                $command->sessionId,
                $command->userId,
            );

            if ($cart->isEmpty()) {
                throw new RuntimeException('O carrinho está vazio.');
            }

            // 1. Valida estoque e captura snapshot dos produtos
            $productSnapshots = [];
            foreach ($cart->items() as $item) {
                $product = ProductModel::find($item->productId());

                if ($product === null || $product->status !== 'active') {
                    throw new RuntimeException(
                        "O produto '{$item->productName()}' não está mais disponível."
                    );
                }

                if ($product->stock < $item->quantity()) {
                    throw new RuntimeException(
                        "Estoque insuficiente para '{$item->productName()}'. "
                        . "Disponível: {$product->stock} unidade(s)."
                    );
                }

                $productSnapshots[$item->productId()] = $product;
            }

            // 2. Calcula totais
            $subtotal     = $cart->subtotal();
            $discount     = Money::fromCentavos(0);
            $shippingCost = Money::fromCentavos(0);
            $coupon       = null;

            if ($cart->couponId() !== null) {
                $coupon = CouponModel::find($cart->couponId());
                if ($coupon && $coupon->isValid($subtotal->centavos())) {
                    $discount = $cart->calculateDiscount($coupon->type, $coupon->value);
                }
            }

            // 2b. Calcula custo de frete a partir da opção interna selecionada
            $shippingServiceCode = $command->shippingServiceCode;
            if ($command->shippingOptionId && str_starts_with($command->shippingOptionId, 'internal_')) {
                $rateId = (int) substr($command->shippingOptionId, strlen('internal_'));
                $rate   = $this->shippingRateRepository->findById($rateId);

                if ($rate !== null && $rate->isActive()) {
                    $totalWeight = 0;
                    foreach ($cart->items() as $item) {
                        $p = $productSnapshots[$item->productId()] ?? null;
                        $totalWeight += ($p?->weight_grams ?? 300) * $item->quantity();
                    }

                    $shippingCost        = Money::fromCentavos($rate->calculatePrice($totalWeight, $subtotal->centavos()));
                    $shippingServiceCode = $rate->serviceCode();
                }
            }

            // 3. Cria a Order e seus itens
            $orderItems = [];
            foreach ($cart->items() as $item) {
                $product      = $productSnapshots[$item->productId()];
                $orderItems[] = OrderItem::create(
                    orderId: 0, // preenchido após save
                    productId: $item->productId(),
                    variantId: $item->variantId(),
                    name: $product->name,
                    sku: $product->sku,
                    unitPrice: $item->unitPrice(),
                    quantity: $item->quantity(),
                );
            }

            $order = Order::create(
                tenantId: $command->tenantId,
                userId: $command->userId,
                subtotal: $subtotal,
                discount: $discount,
                shippingCost: $shippingCost,
                couponId: $coupon?->id,
                paymentMethod: $command->paymentMethod,
                notes: $command->notes,
                items: $orderItems,
                shippingAddress: $command->shippingAddress,
                shippingServiceCode: $shippingServiceCode,
            );

            $savedOrder = $this->orderRepository->save($order);

            // 4. Deduz estoque
            foreach ($cart->items() as $item) {
                ProductModel::where('id', $item->productId())
                    ->decrement('stock', $item->quantity());
            }

            // 5. Registra uso do cupom
            if ($coupon !== null && $discount->centavos() > 0) {
                CouponUsageModel::create([
                    'coupon_id'       => $coupon->id,
                    'user_id'         => $command->userId,
                    'order_id'        => $savedOrder->id(),
                    'discount_amount' => $discount->centavos(),
                ]);

                $coupon->increment('uses_count');
            }

            // 6. Calcula comissões para itens de produtos com seller vinculado
            $this->commissionCalculator->calculateForOrder($savedOrder->id(), $productSnapshots);

            // 7. Limpa o carrinho
            $this->cartRepository->delete($cart->id());

            return $savedOrder;
        });

        // 7. Dispara evento fora da transação
        foreach ($order->pullDomainEvents() as $event) {
            $this->events->dispatch($event);
        }

        $order->recordCreatedEvent();
        foreach ($order->pullDomainEvents() as $event) {
            $this->events->dispatch($event);
        }

        return $order;
    }
}
