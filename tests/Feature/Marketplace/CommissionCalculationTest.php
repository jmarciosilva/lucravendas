<?php

declare(strict_types=1);

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Marketplace\Infrastructure\Models\CommissionModel;
use App\Modules\Marketplace\Infrastructure\Models\SellerModel;

it('checkout cria comissão para produto com seller vinculado', function () {
    $tenantId = 'tenant-comm';
    $user     = loginComo('customer');
    $session  = 'sess-' . uniqid();

    $seller = SellerModel::create([
        'name' => 'Seller Comm', 'slug' => 'seller-comm',
        'tenant_id' => $tenantId, 'status' => 'active', 'commission_rate' => 10,
    ]);

    $product = ProductModel::create([
        'name' => 'Prod Comm', 'slug' => 'prod-comm-' . uniqid(),
        'price' => 10000, 'stock' => 5, 'status' => 'active',
        'tenant_id' => $tenantId, 'seller_id' => $seller->id,
    ]);

    // Adiciona ao carrinho
    $this->postJson('/api/v1/cart/items', [
        'product_id' => $product->id, 'quantity' => 2,
    ], ['X-Tenant-ID' => $tenantId, 'X-Cart-Session' => $session]);

    // Faz checkout
    $this->actingAs($user)
        ->postJson('/api/v1/checkout', [
            'payment_method' => 'pix',
        ], ['X-Tenant-ID' => $tenantId, 'X-Cart-Session' => $session])
        ->assertStatus(201);

    // Gross = 10000 * 2 = 20000; comissão 10% = 2000; líquido = 18000
    $this->assertDatabaseHas('commissions', [
        'seller_id'         => $seller->id,
        'gross_amount'      => 20000,
        'commission_amount' => 2000,
        'net_amount'        => 18000,
        'status'            => 'pending',
    ]);
});

it('checkout não cria comissão para produto sem seller', function () {
    $tenantId = 'tenant-no-comm';
    $user     = loginComo('customer');
    $session  = 'sess-nc-' . uniqid();

    $product = ProductModel::create([
        'name' => 'Prod No Seller', 'slug' => 'prod-ns-' . uniqid(),
        'price' => 5000, 'stock' => 3, 'status' => 'active',
        'tenant_id' => $tenantId,
        // seller_id é null
    ]);

    $this->postJson('/api/v1/cart/items', [
        'product_id' => $product->id, 'quantity' => 1,
    ], ['X-Tenant-ID' => $tenantId, 'X-Cart-Session' => $session]);

    $this->actingAs($user)
        ->postJson('/api/v1/checkout', [
            'payment_method' => 'pix',
        ], ['X-Tenant-ID' => $tenantId, 'X-Cart-Session' => $session])
        ->assertStatus(201);

    $this->assertDatabaseCount('commissions', 0);
});
