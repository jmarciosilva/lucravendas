<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;

describe('API de Produtos', function (): void {

    it('deve listar produtos ativos do tenant', function (): void {
        ProductModel::create([
            'name'      => 'Camiseta Azul',
            'slug'      => 'camiseta-azul',
            'price'     => 4990,
            'stock'     => 10,
            'status'    => 'active',
            'tenant_id' => 'tenant-teste',
        ]);

        $response = $this->getJson('/api/v1/products', [
            'X-Tenant-ID' => 'tenant-teste',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonPath('data.0.name', 'Camiseta Azul')
            // Preço deve ser retornado em reais (49.90), não em centavos
            ->assertJsonPath('data.0.price', 49.90);
    });

    it('não deve exibir produtos de outro tenant', function (): void {
        ProductModel::create([
            'name'      => 'Produto do Tenant B',
            'slug'      => 'produto-tenant-b',
            'price'     => 2000,
            'stock'     => 5,
            'status'    => 'active',
            'tenant_id' => 'tenant-b',
        ]);

        $response = $this->getJson('/api/v1/products', [
            'X-Tenant-ID' => 'tenant-a',
        ]);

        // Tenant A não deve ver nenhum produto do Tenant B
        $response->assertStatus(200)
            ->assertJsonPath('data', []);
    });

    it('não deve exibir produtos em rascunho na listagem pública', function (): void {
        ProductModel::create([
            'name'      => 'Rascunho',
            'slug'      => 'rascunho',
            'price'     => 1000,
            'stock'     => 1,
            'status'    => 'draft',
            'tenant_id' => 'tenant-teste',
        ]);

        $response = $this->getJson('/api/v1/products', [
            'X-Tenant-ID' => 'tenant-teste',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data', []);
    });

    it('deve retornar os detalhes de um produto pelo slug', function (): void {
        ProductModel::create([
            'name'      => 'Tênis Corrida',
            'slug'      => 'tenis-corrida',
            'price'     => 29990,
            'stock'     => 3,
            'status'    => 'active',
            'tenant_id' => 'tenant-teste',
        ]);

        $response = $this->getJson('/api/v1/products/tenis-corrida', [
            'X-Tenant-ID' => 'tenant-teste',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.slug', 'tenis-corrida')
            ->assertJsonPath('data.price', 299.90);
    });

    it('deve retornar 404 para produto inexistente', function (): void {
        $this->getJson('/api/v1/products/inexistente', [
            'X-Tenant-ID' => 'tenant-teste',
        ])->assertStatus(404);
    });

    it('deve criar produto como tenant_admin autenticado', function (): void {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('tenant_admin');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/products', [
                'name'  => 'Produto Novo',
                'price' => 99.90,
                'stock' => 10,
            ], ['X-Tenant-ID' => 'tenant-teste']);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Produto Novo')
            // Novo produto deve sempre iniciar como rascunho
            ->assertJsonPath('data.status', 'draft');
    });

    it('deve atualizar produto existente', function (): void {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('tenant_admin');

        $product = ProductModel::create([
            'name'      => 'Produto Original',
            'slug'      => 'produto-original',
            'price'     => 5000,
            'stock'     => 5,
            'status'    => 'active',
            'tenant_id' => 'tenant-teste',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/products/{$product->id}", [
                'name'  => 'Produto Atualizado',
                'price' => 59.90,
            ], ['X-Tenant-ID' => 'tenant-teste']);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Produto atualizado com sucesso.']);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Produto Atualizado']);
    });

    it('deve remover produto com soft delete', function (): void {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('tenant_admin');

        $product = ProductModel::create([
            'name'      => 'Produto a Remover',
            'slug'      => 'produto-remover',
            'price'     => 1000,
            'stock'     => 1,
            'status'    => 'active',
            'tenant_id' => 'tenant-teste',
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/products/{$product->id}", [], [
                'X-Tenant-ID' => 'tenant-teste',
            ])
            ->assertStatus(200);

        // Soft delete: registro permanece no banco com deleted_at preenchido
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    });

    it('deve recusar criação de produto sem autenticação', function (): void {
        $this->postJson('/api/v1/products', ['name' => 'Sem Auth', 'price' => 10])
            ->assertStatus(401);
    });

    it('deve filtrar produtos por faixa de preço', function (): void {
        ProductModel::create([
            'name' => 'Barato', 'slug' => 'barato',
            'price' => 1000, 'stock' => 1,
            'status' => 'active', 'tenant_id' => 'tenant-teste',
        ]);
        ProductModel::create([
            'name' => 'Caro', 'slug' => 'caro',
            'price' => 50000, 'stock' => 1,
            'status' => 'active', 'tenant_id' => 'tenant-teste',
        ]);

        $response = $this->getJson('/api/v1/products?min_price=5&max_price=20', [
            'X-Tenant-ID' => 'tenant-teste',
        ]);

        $response->assertStatus(200);
        // Apenas o produto "Barato" (R$ 10,00) deve aparecer no intervalo R$ 5–R$ 20
        expect(count($response->json('data')))->toBe(1)
            ->and($response->json('data.0.name'))->toBe('Barato');
    });

});
