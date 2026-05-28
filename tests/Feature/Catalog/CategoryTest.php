<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Catalog\Infrastructure\Models\CategoryModel;

describe('API de Categorias', function (): void {

    it('deve listar a árvore de categorias do tenant', function (): void {
        CategoryModel::create([
            'name'      => 'Roupas',
            'slug'      => 'roupas',
            'tenant_id' => 'tenant-teste',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/categories', [
            'X-Tenant-ID' => 'tenant-teste',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'children']]]);
    });

    it('não deve retornar categorias de outro tenant', function (): void {
        CategoryModel::create([
            'name'      => 'Exclusiva Tenant B',
            'slug'      => 'exclusiva-b',
            'tenant_id' => 'tenant-b',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/categories', [
            'X-Tenant-ID' => 'tenant-a',
        ]);

        // Nenhuma categoria de tenant-b deve aparecer para tenant-a
        $response->assertStatus(200)
            ->assertJsonPath('data', []);
    });

    it('deve criar categoria como tenant_admin autenticado', function (): void {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('tenant_admin');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/categories', [
                'name' => 'Calçados',
            ], ['X-Tenant-ID' => 'tenant-teste']);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Calçados');
    });

    it('deve criar subcategoria vinculada à categoria pai', function (): void {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('tenant_admin');

        $pai = CategoryModel::create([
            'name'      => 'Roupas',
            'slug'      => 'roupas',
            'tenant_id' => 'tenant-teste',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/categories', [
                'name'      => 'Camisetas',
                'parent_id' => $pai->id,
            ], ['X-Tenant-ID' => 'tenant-teste']);

        $response->assertStatus(201)
            ->assertJsonPath('data.parent_id', $pai->id);
    });

    it('deve recusar criação de categoria sem autenticação', function (): void {
        $this->postJson('/api/v1/categories', ['name' => 'Sem Auth'])
            ->assertStatus(401);
    });

});
