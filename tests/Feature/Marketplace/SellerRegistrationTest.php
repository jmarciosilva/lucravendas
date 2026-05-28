<?php

declare(strict_types=1);

use App\Modules\Marketplace\Infrastructure\Models\SellerModel;

it('usuário autenticado pode se cadastrar como seller', function () {
    $user = loginComo('customer');

    $this->actingAs($user)
        ->postJson('/api/v1/sellers/register', [
            'name' => 'Loja do João',
        ], ['X-Tenant-ID' => 'tenant-seller'])
        ->assertStatus(201)
        ->assertJsonPath('data.name', 'Loja do João')
        ->assertJsonPath('data.slug', 'loja-do-joao')
        ->assertJsonPath('data.status', 'pending');

    $this->assertDatabaseHas('sellers', [
        'user_id'   => $user->id,
        'tenant_id' => 'tenant-seller',
        'status'    => 'pending',
    ]);
});

it('seller duplicado retorna 422', function () {
    $user = loginComo('customer');

    // Primeiro cadastro
    $this->actingAs($user)
        ->postJson('/api/v1/sellers/register', ['name' => 'Loja A'], ['X-Tenant-ID' => 'tenant-dup'])
        ->assertStatus(201);

    // Segundo cadastro do mesmo usuário no mesmo tenant
    $this->actingAs($user)
        ->postJson('/api/v1/sellers/register', ['name' => 'Loja B'], ['X-Tenant-ID' => 'tenant-dup'])
        ->assertStatus(422);
});

it('slug duplicado no mesmo tenant retorna 422', function () {
    $userA = loginComo('customer');
    $userB = loginComo('customer');

    $this->actingAs($userA)
        ->postJson('/api/v1/sellers/register', [
            'name' => 'Loja X',
            'slug' => 'loja-x',
        ], ['X-Tenant-ID' => 'tenant-slug'])
        ->assertStatus(201);

    $this->actingAs($userB)
        ->postJson('/api/v1/sellers/register', [
            'name' => 'Loja X Cópia',
            'slug' => 'loja-x',
        ], ['X-Tenant-ID' => 'tenant-slug'])
        ->assertStatus(422);
});

it('cadastro de seller requer autenticação', function () {
    $this->postJson('/api/v1/sellers/register', ['name' => 'Loja'])->assertStatus(401);
});
