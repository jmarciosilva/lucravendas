<?php

declare(strict_types=1);

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Marketplace\Infrastructure\Models\SellerModel;

it('lista apenas sellers ativos do tenant', function () {
    $tenantId = 'tenant-list';

    SellerModel::create(['name' => 'Ativa', 'slug' => 'ativa', 'tenant_id' => $tenantId, 'status' => 'active', 'commission_rate' => 10]);
    SellerModel::create(['name' => 'Pendente', 'slug' => 'pendente', 'tenant_id' => $tenantId, 'status' => 'pending', 'commission_rate' => 10]);
    SellerModel::create(['name' => 'Suspensa', 'slug' => 'suspensa', 'tenant_id' => $tenantId, 'status' => 'suspended', 'commission_rate' => 10]);

    $this->getJson('/api/v1/marketplace/sellers', ['X-Tenant-ID' => $tenantId])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Ativa');
});

it('exibe perfil de seller ativo pelo slug', function () {
    $tenantId = 'tenant-profile';

    SellerModel::create([
        'name' => 'Seller Perfil', 'slug' => 'seller-perfil',
        'tenant_id' => $tenantId, 'status' => 'active',
        'commission_rate' => 10, 'description' => 'Descrição da loja',
    ]);

    $this->getJson('/api/v1/marketplace/sellers/seller-perfil', ['X-Tenant-ID' => $tenantId])
        ->assertOk()
        ->assertJsonPath('data.slug', 'seller-perfil')
        ->assertJsonPath('data.description', 'Descrição da loja');
});

it('seller inativo retorna 404 no perfil', function () {
    SellerModel::create([
        'name' => 'Inativa', 'slug' => 'inativa',
        'tenant_id' => 'tenant-404', 'status' => 'suspended', 'commission_rate' => 10,
    ]);

    $this->getJson('/api/v1/marketplace/sellers/inativa', ['X-Tenant-ID' => 'tenant-404'])
        ->assertStatus(404);
});

it('lista produtos de um seller ativo', function () {
    $tenantId = 'tenant-prod-list';

    $seller = SellerModel::create([
        'name' => 'Seller Produtos', 'slug' => 'seller-produtos',
        'tenant_id' => $tenantId, 'status' => 'active', 'commission_rate' => 10,
    ]);

    ProductModel::create([
        'name' => 'Produto do Seller', 'slug' => 'prod-seller-' . uniqid(),
        'price' => 5000, 'stock' => 10, 'status' => 'active',
        'tenant_id' => $tenantId, 'seller_id' => $seller->id,
    ]);

    $this->getJson("/api/v1/marketplace/sellers/seller-produtos/products", ['X-Tenant-ID' => $tenantId])
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Produto do Seller');
});
