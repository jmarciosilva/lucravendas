<?php

declare(strict_types=1);

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Orders\Infrastructure\Models\CouponModel;
use Illuminate\Support\Carbon;

// Helper: cria produto ativo com tenant fixo
function criarProdutoCartTest(int $priceCentavos = 5000, int $stock = 10, string $tenantId = 'tenant-cart'): ProductModel
{
    return ProductModel::create([
        'name'      => 'Produto Teste ' . uniqid(),
        'slug'      => 'produto-teste-' . uniqid(),
        'price'     => $priceCentavos,
        'stock'     => $stock,
        'status'    => 'active',
        'tenant_id' => $tenantId,
    ]);
}

it('visitante anônimo pode adicionar item ao carrinho via X-Cart-Session', function () {
    $product = criarProdutoCartTest();

    $this->postJson('/api/v1/cart/items', [
        'product_id' => $product->id,
        'quantity'   => 2,
    ], ['X-Tenant-ID' => 'tenant-cart', 'X-Cart-Session' => 'session-anon-1'])
        ->assertStatus(201)
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.quantity', 2);
});

it('retorna o carrinho pelo mesmo session_id', function () {
    $product = criarProdutoCartTest(tenantId: 'tenant-get');
    $headers = ['X-Tenant-ID' => 'tenant-get', 'X-Cart-Session' => 'session-get-1'];

    $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], $headers);

    $this->getJson('/api/v1/cart', $headers)
        ->assertOk()
        ->assertJsonPath('data.items.0.product_id', $product->id);
});

it('merge do carrinho anônimo ao carrinho do usuário autenticado', function () {
    $product = criarProdutoCartTest(tenantId: 'tenant-merge');
    $user    = loginComo('customer');
    $headers = ['X-Tenant-ID' => 'tenant-merge', 'X-Cart-Session' => 'session-merge-1'];

    // Adiciona sem autenticação
    $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], $headers);

    // Usuário autenticado pega o carrinho com o mesmo session_id → merge
    $this->actingAs($user)->getJson('/api/v1/cart', $headers)
        ->assertOk()
        ->assertJsonPath('data.items.0.product_id', $product->id);
});

it('atualiza a quantidade de um item do carrinho', function () {
    $product = criarProdutoCartTest(stock: 20, tenantId: 'tenant-upd');
    $headers = ['X-Tenant-ID' => 'tenant-upd', 'X-Cart-Session' => 'session-upd-1'];

    $addResp = $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], $headers);
    $itemId  = $addResp->json('data.items.0.id');

    $this->putJson("/api/v1/cart/items/{$itemId}", ['quantity' => 3], $headers)
        ->assertOk()
        ->assertJsonPath('data.items.0.quantity', 3);
});

it('remove um item do carrinho', function () {
    $product = criarProdutoCartTest(tenantId: 'tenant-rem');
    $headers = ['X-Tenant-ID' => 'tenant-rem', 'X-Cart-Session' => 'session-rem-1'];

    $addResp = $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], $headers);
    $itemId  = $addResp->json('data.items.0.id');

    $this->deleteJson("/api/v1/cart/items/{$itemId}", [], $headers)
        ->assertOk()
        ->assertJsonPath('data.is_empty', true);
});

it('aplica cupom percentual válido ao carrinho', function () {
    $product = criarProdutoCartTest(10000, tenantId: 'tenant-coup');
    $headers = ['X-Tenant-ID' => 'tenant-coup', 'X-Cart-Session' => 'session-coup-1'];

    CouponModel::create([
        'tenant_id' => 'tenant-coup',
        'code'      => 'DESCONTO10',
        'type'      => 'percent',
        'value'     => 10,
        'is_active' => true,
    ]);

    $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], $headers);

    $this->postJson('/api/v1/cart/coupon', ['code' => 'DESCONTO10'], $headers)
        ->assertOk()
        ->assertJsonPath('data.coupon.code', 'DESCONTO10')
        ->assertJsonPath('data.discount', fn ($v) => $v == 10);
});

it('retorna 422 para cupom expirado', function () {
    $product = criarProdutoCartTest(tenantId: 'tenant-exp');
    $headers = ['X-Tenant-ID' => 'tenant-exp', 'X-Cart-Session' => 'session-exp-1'];

    CouponModel::create([
        'tenant_id'  => 'tenant-exp',
        'code'       => 'EXPIRADO',
        'type'       => 'percent',
        'value'      => 10,
        'is_active'  => true,
        'expires_at' => Carbon::yesterday(),
    ]);

    $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], $headers);

    $this->postJson('/api/v1/cart/coupon', ['code' => 'EXPIRADO'], $headers)
        ->assertStatus(422);
});

it('retorna 422 para cupom com valor mínimo não atingido', function () {
    $product = criarProdutoCartTest(5000, tenantId: 'tenant-min'); // R$ 50
    $headers = ['X-Tenant-ID' => 'tenant-min', 'X-Cart-Session' => 'session-min-1'];

    CouponModel::create([
        'tenant_id'       => 'tenant-min',
        'code'            => 'MINIMO',
        'type'            => 'fixed',
        'value'           => 1000,
        'min_order_value' => 20000,
        'is_active'       => true,
    ]);

    $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], $headers);

    $this->postJson('/api/v1/cart/coupon', ['code' => 'MINIMO'], $headers)
        ->assertStatus(422)
        ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'mínimo'));
});

it('retorna 422 ao adicionar produto sem estoque', function () {
    $product = criarProdutoCartTest(5000, 0, 'tenant-ns'); // sem estoque
    $headers = ['X-Tenant-ID' => 'tenant-ns', 'X-Cart-Session' => 'session-ns-1'];

    $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], $headers)
        ->assertStatus(422);
});
