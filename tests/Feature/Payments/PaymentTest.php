<?php

declare(strict_types=1);

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Orders\Infrastructure\Models\OrderModel;
use App\Modules\Payments\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Infrastructure\Models\PaymentTransactionModel;

// Helper: cria pedido confirmado com produto no banco
function criarPedidoParaPagamento(string $tenantId = 'tenant-pay'): array
{
    $product = ProductModel::create([
        'name' => 'Prod Pay ' . uniqid(), 'slug' => 'prod-pay-' . uniqid(),
        'price' => 10000, 'stock' => 5, 'status' => 'active', 'tenant_id' => $tenantId,
    ]);

    $user  = loginComo('customer');
    $order = OrderModel::create([
        'tenant_id'      => $tenantId,
        'user_id'        => $user->id,
        'status'         => 'pending',
        'subtotal'       => 10000,
        'discount'       => 0,
        'shipping_cost'  => 0,
        'total'          => 10000,
        'payment_status' => 'pending',
    ]);

    return compact('user', 'order', 'product', 'tenantId');
}

it('gera cobrança PIX com qr_code', function () {
    ['user' => $user, 'order' => $order, 'tenantId' => $tenantId] = criarPedidoParaPagamento('tenant-pix');

    // Mock do gateway para evitar chamadas reais ao Mercado Pago
    $this->mock(PaymentGatewayInterface::class)
        ->shouldReceive('createPixPayment')
        ->once()
        ->andReturn([
            'external_id'    => 'mp-pix-123',
            'status'         => 'pending',
            'qr_code'        => 'pix-qr-code-string',
            'qr_code_base64' => 'base64imagepix==',
        ]);

    $this->actingAs($user)
        ->postJson('/api/v1/payments/pix', ['order_id' => $order->id], ['X-Tenant-ID' => $tenantId])
        ->assertStatus(201)
        ->assertJsonPath('data.method', 'pix')
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.qr_code', 'pix-qr-code-string');
});

it('processa cartão e retorna status approved', function () {
    ['user' => $user, 'order' => $order, 'tenantId' => $tenantId] = criarPedidoParaPagamento('tenant-card');

    $this->mock(PaymentGatewayInterface::class)
        ->shouldReceive('createCardPayment')
        ->once()
        ->andReturn([
            'external_id'  => 'mp-card-456',
            'status'       => 'approved',
            'installments' => 1,
        ]);

    $this->actingAs($user)
        ->postJson('/api/v1/payments/card', [
            'order_id'     => $order->id,
            'card_token'   => 'card-token-abc',
            'installments' => 1,
            'payer_email'  => $user->email,
        ], ['X-Tenant-ID' => $tenantId])
        ->assertStatus(201)
        ->assertJsonPath('data.method', 'credit_card')
        ->assertJsonPath('data.status', 'approved');

    // Pedido deve ter sido confirmado automaticamente
    $this->assertDatabaseHas('orders', [
        'id'             => $order->id,
        'payment_status' => 'paid',
        'status'         => 'confirmed',
    ]);
});

it('gera boleto com ticket_url', function () {
    ['user' => $user, 'order' => $order, 'tenantId' => $tenantId] = criarPedidoParaPagamento('tenant-boleto');

    $this->mock(PaymentGatewayInterface::class)
        ->shouldReceive('createBoletoPayment')
        ->once()
        ->andReturn([
            'external_id' => 'mp-boleto-789',
            'status'      => 'pending',
            'ticket_url'  => 'https://boleto.example.com/pdf',
        ]);

    $this->actingAs($user)
        ->postJson('/api/v1/payments/boleto', [
            'order_id'    => $order->id,
            'payer_email' => $user->email,
            'payer_cpf'   => '123.456.789-09',
        ], ['X-Tenant-ID' => $tenantId])
        ->assertStatus(201)
        ->assertJsonPath('data.method', 'boleto')
        ->assertJsonPath('data.ticket_url', 'https://boleto.example.com/pdf');
});

it('webhook com assinatura inválida retorna 401', function () {
    // Configura um secret para forçar validação
    config(['payments.mercadopago.webhook_secret' => 'secret-test']);

    $this->postJson('/api/v1/webhooks/mercadopago', [
        'data' => ['id' => '99999'],
        'type' => 'payment',
    ], ['x-signature' => 'ts=invalid,v1=badsig', 'x-request-id' => 'req-1'])
        ->assertStatus(401);
});

it('webhook approved atualiza pedido para paid e confirmed', function () {
    ['user' => $user, 'order' => $order, 'tenantId' => $tenantId] = criarPedidoParaPagamento('tenant-wh');

    PaymentTransactionModel::create([
        'order_id'    => $order->id,
        'gateway'     => 'mercadopago',
        'external_id' => 'mp-wh-001',
        'method'      => 'pix',
        'amount'      => 10000,
        'status'      => 'pending',
    ]);

    // Sem secret configurado, a validação de assinatura é ignorada
    config(['payments.mercadopago.webhook_secret' => '']);

    $this->mock(PaymentGatewayInterface::class)
        ->shouldReceive('getPaymentStatus')
        ->once()
        ->with('mp-wh-001')
        ->andReturn('approved');

    $this->postJson('/api/v1/webhooks/mercadopago', [
        'data' => ['id' => 'mp-wh-001'],
        'type' => 'payment',
    ])->assertOk();

    $this->assertDatabaseHas('orders', [
        'id'             => $order->id,
        'payment_status' => 'paid',
        'status'         => 'confirmed',
    ]);
});

it('webhook rejected cancela pedido e restaura estoque', function () {
    $product = ProductModel::create([
        'name' => 'Prod WH Rej ' . uniqid(), 'slug' => 'prod-whr-' . uniqid(),
        'price' => 5000, 'stock' => 3, 'status' => 'active', 'tenant_id' => 'tenant-rej',
    ]);

    $user  = loginComo('customer');
    $order = OrderModel::create([
        'tenant_id' => 'tenant-rej', 'user_id' => $user->id,
        'status' => 'pending', 'subtotal' => 5000, 'discount' => 0,
        'shipping_cost' => 0, 'total' => 5000, 'payment_status' => 'pending',
    ]);

    // Cria item do pedido para que o estoque possa ser restaurado
    \App\Modules\Orders\Infrastructure\Models\OrderItemModel::create([
        'order_id'   => $order->id,
        'product_id' => $product->id,
        'name'       => $product->name,
        'unit_price' => 5000,
        'quantity'   => 2,
        'total'      => 10000,
    ]);

    PaymentTransactionModel::create([
        'order_id' => $order->id, 'gateway' => 'mercadopago',
        'external_id' => 'mp-wh-rej', 'method' => 'credit_card',
        'amount' => 5000, 'status' => 'pending',
    ]);

    config(['payments.mercadopago.webhook_secret' => '']);

    $this->mock(PaymentGatewayInterface::class)
        ->shouldReceive('getPaymentStatus')
        ->once()
        ->andReturn('rejected');

    $this->postJson('/api/v1/webhooks/mercadopago', [
        'data' => ['id' => 'mp-wh-rej'], 'type' => 'payment',
    ])->assertOk();

    $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'failed', 'status' => 'cancelled']);
    // Estoque restaurado: 3 + 2 = 5
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
});

it('endpoints de pagamento requerem autenticação', function () {
    $this->postJson('/api/v1/payments/pix', ['order_id' => 1])->assertStatus(401);
    $this->postJson('/api/v1/payments/card', [])->assertStatus(401);
    $this->postJson('/api/v1/payments/boleto', [])->assertStatus(401);
});
