<?php

declare(strict_types=1);

use App\Modules\Orders\Infrastructure\Models\OrderModel;

it('webhook de rastreio atualiza o status do pedido', function () {
    $user = loginComo('customer');

    $order = OrderModel::create([
        'tenant_id'      => 'tenant-track',
        'user_id'        => $user->id,
        'status'         => 'confirmed',
        'subtotal'       => 5000,
        'discount'       => 0,
        'shipping_cost'  => 1500,
        'total'          => 6500,
        'payment_status' => 'paid',
        'tracking_code'  => 'BR999999999BR',
    ]);

    $this->postJson('/api/v1/webhooks/shipping', [
        'tracking' => 'BR999999999BR',
        'status'   => ['event' => 'in_transit', 'description' => 'Objeto em trânsito'],
    ])->assertOk()->assertJsonPath('ok', true);

    $this->assertDatabaseHas('orders', [
        'id'              => $order->id,
        'tracking_status' => 'in_transit',
    ]);
});

it('webhook de rastreio com status entregue transiciona pedido para delivered', function () {
    $user = loginComo('customer');

    $order = OrderModel::create([
        'tenant_id'      => 'tenant-delivered',
        'user_id'        => $user->id,
        'status'         => 'shipped',
        'subtotal'       => 5000, 'discount' => 0,
        'shipping_cost'  => 1500, 'total'    => 6500,
        'payment_status' => 'paid',
        'tracking_code'  => 'BR888888888BR',
    ]);

    $this->postJson('/api/v1/webhooks/shipping', [
        'tracking' => 'BR888888888BR',
        'status'   => ['event' => 'delivered'],
    ])->assertOk();

    $this->assertDatabaseHas('orders', [
        'id'              => $order->id,
        'tracking_status' => 'delivered',
        'status'          => 'delivered',
    ]);
});

it('webhook com código de rastreio inexistente retorna ok false', function () {
    $this->postJson('/api/v1/webhooks/shipping', [
        'tracking' => 'INVALIDO999',
        'status'   => ['event' => 'in_transit'],
    ])->assertOk()->assertJsonPath('ok', false);
});
