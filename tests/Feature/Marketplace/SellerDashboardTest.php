<?php

declare(strict_types=1);

use App\Modules\Marketplace\Infrastructure\Models\CommissionModel;
use App\Modules\Marketplace\Infrastructure\Models\SellerModel;
use App\Modules\Orders\Infrastructure\Models\OrderItemModel;
use App\Modules\Orders\Infrastructure\Models\OrderModel;

it('seller autenticado visualiza seu dashboard', function () {
    $user     = loginComo('customer');
    $tenantId = 'tenant-dash';

    SellerModel::create([
        'name' => 'Loja Dashboard', 'slug' => 'loja-dash',
        'tenant_id' => $tenantId, 'user_id' => $user->id,
        'status' => 'active', 'commission_rate' => 10,
    ]);

    $this->actingAs($user)
        ->getJson('/api/v1/seller/dashboard', ['X-Tenant-ID' => $tenantId])
        ->assertOk()
        ->assertJsonPath('data.seller.name', 'Loja Dashboard')
        ->assertJsonPath('data.metrics.total_orders', 0)
        ->assertJsonPath('data.metrics.pending_commission_centavos', 0);
});

it('usuário sem seller recebe 404 no dashboard', function () {
    $user = loginComo('customer');

    $this->actingAs($user)
        ->getJson('/api/v1/seller/dashboard', ['X-Tenant-ID' => 'tenant-sem-seller'])
        ->assertStatus(404);
});

it('dashboard requer autenticação', function () {
    $this->getJson('/api/v1/seller/dashboard')->assertStatus(401);
});
