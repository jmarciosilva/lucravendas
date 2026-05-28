<?php

declare(strict_types=1);

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Shipping\Domain\Contracts\ShippingGatewayInterface;
use App\Modules\Shipping\Domain\ValueObjects\ShippingOption;
use App\Modules\Shipping\Infrastructure\Models\ShippingRateModel;
use App\Modules\Shipping\Infrastructure\Models\ShippingZoneModel;

// Helper: cria zona e tarifa de frete para um tenant
function criarZonaTarifa(string $tenantId, array $states = ['SP'], int $basePrice = 1500, ?int $threshold = null): array
{
    $zone = ShippingZoneModel::create([
        'tenant_id' => $tenantId,
        'name'      => 'Zona Teste',
        'states'    => $states,
        'is_active' => true,
    ]);

    $rate = ShippingRateModel::create([
        'zone_id'                 => $zone->id,
        'tenant_id'               => $tenantId,
        'name'                    => 'Tarifa Teste',
        'carrier'                 => 'custom',
        'base_price'              => $basePrice,
        'price_per_kg'            => 0,
        'min_days'                => 3,
        'max_days'                => 7,
        'free_shipping_threshold' => $threshold,
        'is_active'               => true,
    ]);

    return compact('zone', 'rate');
}

it('retorna opções de frete para CEP em zona configurada', function () {
    $tenantId = 'tenant-calc';
    $user     = loginComo('customer');
    $session  = 'sess-calc-' . uniqid();

    criarZonaTarifa($tenantId, ['SP'], 1500);

    $product = ProductModel::create([
        'name' => 'Prod Frete', 'slug' => 'prod-frete-' . uniqid(),
        'price' => 5000, 'stock' => 10, 'status' => 'active', 'tenant_id' => $tenantId,
    ]);

    // Adiciona item ao carrinho
    $this->postJson('/api/v1/cart/items', [
        'product_id' => $product->id, 'quantity' => 1,
    ], ['X-Tenant-ID' => $tenantId, 'X-Cart-Session' => $session]);

    // Mock ME para retornar vazio (evitar chamada real à API)
    $this->mock(ShippingGatewayInterface::class)
        ->shouldReceive('calculateRates')
        ->andReturn([]);

    $this->getJson('/api/v1/shipping/calculate?zipcode=01310-100&state=SP', [
        'X-Tenant-ID'    => $tenantId,
        'X-Cart-Session' => $session,
    ])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.carrier', 'custom')
        ->assertJsonPath('data.0.price_centavos', 1500);
});

it('retorna opções do Melhor Envio quando gateway está configurado', function () {
    $tenantId = 'tenant-me';
    $user     = loginComo('customer');
    $session  = 'sess-me-' . uniqid();

    $product = ProductModel::create([
        'name' => 'Prod ME', 'slug' => 'prod-me-' . uniqid(),
        'price' => 8000, 'stock' => 5, 'status' => 'active', 'tenant_id' => $tenantId,
    ]);

    $this->postJson('/api/v1/cart/items', [
        'product_id' => $product->id, 'quantity' => 1,
    ], ['X-Tenant-ID' => $tenantId, 'X-Cart-Session' => $session]);

    // Simula resposta do ME com duas opções
    $meOptions = [
        new ShippingOption('me_1', 'PAC — Correios', 'melhorenvio', '1', 1870, 8, 8, false),
        new ShippingOption('me_2', 'SEDEX — Correios', 'melhorenvio', '2', 3200, 2, 2, false),
    ];

    $this->mock(ShippingGatewayInterface::class)
        ->shouldReceive('calculateRates')
        ->once()
        ->andReturn($meOptions);

    config(['shipping.melhorenvio.token' => 'fake-token', 'shipping.gateway' => 'melhorenvio']);

    $this->getJson('/api/v1/shipping/calculate?zipcode=20040-020&state=RJ', [
        'X-Tenant-ID'    => $tenantId,
        'X-Cart-Session' => $session,
    ])
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('retorna frete grátis quando subtotal atinge o threshold', function () {
    $tenantId = 'tenant-free';
    $session  = 'sess-free-' . uniqid();

    // Frete grátis acima de R$ 100,00 (10000 centavos)
    criarZonaTarifa($tenantId, ['MG'], 1500, 10000);

    $product = ProductModel::create([
        'name' => 'Prod Grátis', 'slug' => 'prod-gratis-' . uniqid(),
        'price' => 12000, 'stock' => 5, 'status' => 'active', 'tenant_id' => $tenantId,
    ]);

    $this->postJson('/api/v1/cart/items', [
        'product_id' => $product->id, 'quantity' => 1,
    ], ['X-Tenant-ID' => $tenantId, 'X-Cart-Session' => $session]);

    $this->mock(ShippingGatewayInterface::class)->shouldReceive('calculateRates')->andReturn([]);

    $this->getJson('/api/v1/shipping/calculate?zipcode=30110-001&state=MG', [
        'X-Tenant-ID'    => $tenantId,
        'X-Cart-Session' => $session,
    ])
        ->assertOk()
        ->assertJsonPath('data.0.price_centavos', 0)
        ->assertJsonPath('data.0.is_free_shipping', true);
});

it('retorna lista vazia para CEP fora das zonas configuradas', function () {
    $tenantId = 'tenant-vazio';
    $session  = 'sess-vz-' . uniqid();

    // Zona só para SP
    criarZonaTarifa($tenantId, ['SP'], 1500);

    $product = ProductModel::create([
        'name' => 'Prod Vazio', 'slug' => 'prod-vz-' . uniqid(),
        'price' => 3000, 'stock' => 5, 'status' => 'active', 'tenant_id' => $tenantId,
    ]);

    $this->postJson('/api/v1/cart/items', [
        'product_id' => $product->id, 'quantity' => 1,
    ], ['X-Tenant-ID' => $tenantId, 'X-Cart-Session' => $session]);

    $this->mock(ShippingGatewayInterface::class)->shouldReceive('calculateRates')->andReturn([]);

    $this->getJson('/api/v1/shipping/calculate?zipcode=90110-001&state=RS', [
        'X-Tenant-ID'    => $tenantId,
        'X-Cart-Session' => $session,
    ])
        ->assertOk()
        ->assertJsonCount(0, 'data');
});
