<?php

declare(strict_types=1);

use App\Modules\Catalog\Infrastructure\Models\ProductModel;

it('checkout registra endereço de entrega no pedido', function () {
    $tenantId = 'tenant-chk-ship';
    $user     = loginComo('customer');
    $session  = 'sess-chs-' . uniqid();

    $product = ProductModel::create([
        'name' => 'Prod Ship', 'slug' => 'prod-ship-' . uniqid(),
        'price' => 5000, 'stock' => 5, 'status' => 'active', 'tenant_id' => $tenantId,
    ]);

    $this->postJson('/api/v1/cart/items', [
        'product_id' => $product->id, 'quantity' => 1,
    ], ['X-Tenant-ID' => $tenantId, 'X-Cart-Session' => $session]);

    $this->actingAs($user)
        ->postJson('/api/v1/checkout', [
            'payment_method'       => 'pix',
            'recipient_name'       => 'João Silva',
            'recipient_zipcode'    => '01310-100',
            'recipient_address'    => 'Avenida Paulista',
            'recipient_number'     => '1578',
            'recipient_complement' => 'Apto 42',
            'recipient_city'       => 'São Paulo',
            'recipient_state'      => 'SP',
        ], ['X-Tenant-ID' => $tenantId, 'X-Cart-Session' => $session])
        ->assertStatus(201);

    $this->assertDatabaseHas('orders', [
        'tenant_id'        => $tenantId,
        'recipient_name'   => 'João Silva',
        'recipient_state'  => 'SP',
        'recipient_city'   => 'São Paulo',
    ]);
});

it('checkout com opção de frete interna aplica custo correto', function () {
    $tenantId = 'tenant-chk-rate';
    $user     = loginComo('customer');
    $session  = 'sess-cr-' . uniqid();

    ['rate' => $rate] = criarZonaTarifa($tenantId, ['RJ'], 2000);

    $product = ProductModel::create([
        'name' => 'Prod Rate', 'slug' => 'prod-rate-' . uniqid(),
        'price' => 6000, 'stock' => 5, 'status' => 'active', 'tenant_id' => $tenantId,
    ]);

    $this->postJson('/api/v1/cart/items', [
        'product_id' => $product->id, 'quantity' => 1,
    ], ['X-Tenant-ID' => $tenantId, 'X-Cart-Session' => $session]);

    $this->actingAs($user)
        ->postJson('/api/v1/checkout', [
            'payment_method'    => 'pix',
            'shipping_option_id' => 'internal_' . $rate->id,
            'recipient_name'    => 'Maria', 'recipient_zipcode' => '20040-020',
            'recipient_address' => 'Rua X', 'recipient_number' => '1',
            'recipient_city'    => 'Rio de Janeiro', 'recipient_state' => 'RJ',
        ], ['X-Tenant-ID' => $tenantId, 'X-Cart-Session' => $session])
        ->assertStatus(201);

    // Subtotal = 6000, frete = 2000, total = 8000
    $this->assertDatabaseHas('orders', [
        'tenant_id'     => $tenantId,
        'shipping_cost' => 2000,
        'total'         => 8000,
    ]);
});
