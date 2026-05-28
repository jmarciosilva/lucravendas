<?php

declare(strict_types=1);

namespace App\Modules\Orders\Infrastructure\Repositories;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Orders\Domain\Entities\Order;
use App\Modules\Orders\Domain\Entities\OrderItem;
use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Orders\Domain\ValueObjects\OrderStatus;
use App\Modules\Orders\Domain\ValueObjects\PaymentStatus;
use App\Modules\Orders\Infrastructure\Models\OrderItemModel;
use App\Modules\Orders\Infrastructure\Models\OrderModel;
use App\Modules\Orders\Infrastructure\Models\OrderStatusHistoryModel;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function save(Order $order): Order
    {
        $model = OrderModel::create([
            'tenant_id'      => $order->tenantId(),
            'user_id'        => $order->userId(),
            'status'         => $order->status()->value(),
            'subtotal'       => $order->subtotal()->centavos(),
            'discount'       => $order->discount()->centavos(),
            'shipping_cost'  => $order->shippingCost()->centavos(),
            'total'          => $order->total()->centavos(),
            'payment_method' => $order->paymentMethod(),
            'payment_status' => $order->paymentStatus()->value(),
            'coupon_id'      => $order->couponId(),
            'notes'          => $order->notes(),
        ]);

        foreach ($order->items() as $item) {
            OrderItemModel::create([
                'order_id'   => $model->id,
                'product_id' => $item->productId(),
                'variant_id' => $item->variantId(),
                'name'       => $item->name(),
                'sku'        => $item->sku(),
                'unit_price' => $item->unitPrice()->centavos(),
                'quantity'   => $item->quantity(),
                'total'      => $item->total()->centavos(),
            ]);
        }

        // Registra o status inicial no histórico
        OrderStatusHistoryModel::create([
            'order_id' => $model->id,
            'status'   => $model->status,
            'notes'    => 'Pedido criado.',
        ]);

        $model->load('items');

        return $this->toDomain($model);
    }

    public function update(Order $order): void
    {
        OrderModel::where('id', $order->id())->update([
            'status'         => $order->status()->value(),
            'payment_status' => $order->paymentStatus()->value(),
        ]);
    }

    public function findById(int $id, string $tenantId): ?Order
    {
        $model = OrderModel::with('items')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByIdRaw(int $id): ?Order
    {
        $model = OrderModel::with('items')->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByUser(int $userId, string $tenantId): array
    {
        return OrderModel::with('items')
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (OrderModel $m) => $this->toDomain($m))
            ->all();
    }

    private function toDomain(OrderModel $model): Order
    {
        $items = $model->items->map(fn (OrderItemModel $i) => OrderItem::create(
            orderId: $model->id,
            productId: $i->product_id,
            variantId: $i->variant_id,
            name: $i->name,
            sku: $i->sku,
            unitPrice: Money::fromCentavos($i->unit_price),
            quantity: $i->quantity,
        ))->all();

        return Order::restore(
            id: $model->id,
            tenantId: $model->tenant_id,
            userId: $model->user_id,
            status: OrderStatus::from($model->status),
            subtotal: Money::fromCentavos($model->subtotal),
            discount: Money::fromCentavos($model->discount),
            shippingCost: Money::fromCentavos($model->shipping_cost),
            total: Money::fromCentavos($model->total),
            paymentStatus: PaymentStatus::from($model->payment_status),
            couponId: $model->coupon_id,
            paymentMethod: $model->payment_method,
            notes: $model->notes,
            items: $items,
        );
    }
}
