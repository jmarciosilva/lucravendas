<?php

declare(strict_types=1);

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Orders\Infrastructure\Models\CouponModel;

function criarProdutoCheckout(int $price = 10000, int $stock = 5, string $tenantId = 'tenant-chk'): ProductModel
{
    return ProductModel::create([
        'name'      => 'Produto Checkout ' . uniqid(),
        'slug'      => 'prod-chk-' . uniqid(),
        'price'     => $price,
        'stock'     => $stock,
        'status'    => 'active',
        'tenant_id' => $tenantId,
    ]);
}

it('checkout cria pedido, deduz estoque e limpa o carrinho', function () {
    $product = criarProdutoCheckout(10000, 5, 'tenant-chk-1');
    $user    = loginComo('customer');
    $headers = ['X-Tenant-ID' => 'tenant-chk-1', 'X-Cart-Session' => 'session-chk-1'];

    $this->actingAs($user)->postJson('/api/v1/cart/items', [
        'product_id' => $product->id, 'quantity' => 2,
    ], $headers)->assertStatus(201);

    $response = $this->actingAs($user)->postJson('/api/v1/checkout', [], $headers);

    $response->assertStatus(201)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.items.0.product_id', $product->id);

    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);

    $this->actingAs($user)->getJson('/api/v1/cart', $headers)
        ->assertJsonPath('data.is_empty', true);
});

it('checkout falha quando produto sem estoque suficiente', function () {
    $product = criarProdutoCheckout(5000, 1, 'tenant-oos-1');
    $user    = loginComo('customer');
    $headers = ['X-Tenant-ID' => 'tenant-oos-1', 'X-Cart-Session' => 'session-oos-1'];

    $this->actingAs($user)->postJson('/api/v1/cart/items', [
        'product_id' => $product->id, 'quantity' => 1,
    ], $headers);

    // Simula concorrência: esgota o estoque
    $product->update(['stock' => 0]);

    $this->actingAs($user)->postJson('/api/v1/checkout', [], $headers)
        ->assertStatus(422)
        ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'Estoque insuficiente'));
});

it('checkout aplica desconto do cupom corretamente', function () {
    $product = criarProdutoCheckout(10000, 5, 'tenant-cchk-1');
    $user    = loginComo('customer');
    $headers = ['X-Tenant-ID' => 'tenant-cchk-1', 'X-Cart-Session' => 'session-cchk-1'];

    CouponModel::create([
        'tenant_id' => 'tenant-cchk-1',
        'code'      => 'DESC20',
        'type'      => 'percent',
        'value'     => 20,
        'is_active' => true,
    ]);

    $this->actingAs($user)->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], $headers);
    $this->actingAs($user)->postJson('/api/v1/cart/coupon', ['code' => 'DESC20'], $headers);

    $this->actingAs($user)->postJson('/api/v1/checkout', [], $headers)
        ->assertStatus(201)
        ->assertJsonPath('data.subtotal', fn ($v) => $v == 100)
        ->assertJsonPath('data.discount', fn ($v) => $v == 20)
        ->assertJsonPath('data.total', fn ($v) => $v == 80);
});

it('checkout requer autenticação', function () {
    $this->postJson('/api/v1/checkout', [])->assertStatus(401);
});

it('usuário pode listar seus pedidos', function () {
    $product = criarProdutoCheckout(5000, 5, 'tenant-list-1');
    $user    = loginComo('customer');
    $headers = ['X-Tenant-ID' => 'tenant-list-1', 'X-Cart-Session' => 'session-list-1'];

    $this->actingAs($user)->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], $headers);
    $this->actingAs($user)->postJson('/api/v1/checkout', [], $headers);

    $this->actingAs($user)->getJson('/api/v1/orders', $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data');
});
