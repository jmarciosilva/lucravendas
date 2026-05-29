<?php

declare(strict_types=1);

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Storefront\Presentation\Livewire\AdicionarAoCarrinho;
use App\Modules\Storefront\Presentation\Livewire\CarrinhoPage;
use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

// Insere diretamente via DB para contornar a serialização especial do stancl/tenancy
function criarTenantStorefront(string $slug = 'loja-teste', string $status = 'active'): TenantModel
{
    $id = 'tenant-sf-' . $slug;
    DB::table('tenants')->insert([
        'id'         => $id,
        'name'       => 'Loja Teste',
        'slug'       => $slug,
        'plan'       => 'free',
        'status'     => $status,
        'data'       => json_encode([]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return TenantModel::find($id);
}

function criarProdutoStorefront(TenantModel $tenant, string $slug = null): ProductModel
{
    return ProductModel::create([
        'name'      => 'Produto Storefront',
        'slug'      => $slug ?? 'produto-sf-' . uniqid(),
        'price'     => 5990,
        'stock'     => 10,
        'status'    => 'active',
        'tenant_id' => $tenant->id,
    ]);
}

describe('Vitrine do Cliente (Storefront)', function (): void {

    // Desabilita o Vite em todos os testes desta suite (não há build gerado no CI/testes)
    beforeEach(fn () => test()->withoutVite());

    it('home da loja carrega com tenant válido', function (): void {
        $tenant = criarTenantStorefront('loja-home');

        $response = $this->get("/loja/{$tenant->slug}");

        $response->assertStatus(200)
                 ->assertSee($tenant->name);
    });

    it('slug inválido retorna 404', function (): void {
        $response = $this->get('/loja/loja-que-nao-existe');

        $response->assertStatus(404);
    });

    it('produto ativo aparece no catálogo público', function (): void {
        $tenant  = criarTenantStorefront('loja-catalogo');
        $produto = criarProdutoStorefront($tenant);

        $response = $this->get("/loja/{$tenant->slug}/produtos");

        $response->assertStatus(200)
                 ->assertSee($produto->name);
    });

    it('loja inativa retorna 404', function (): void {
        criarTenantStorefront('loja-inativa', 'inactive');

        $this->get('/loja/loja-inativa')->assertStatus(404);
    });

    it('adicionar ao carrinho via Livewire adiciona item com sucesso', function (): void {
        $tenant  = criarTenantStorefront('loja-add-cart');
        $produto = criarProdutoStorefront($tenant, 'prod-add-cart');

        // Simula a requisição dentro do contexto da loja (middleware vincula o tenant)
        app()->instance('storefront.tenant', $tenant);
        session(['cart_session_id' => 'sf-session-livewire']);

        Livewire::test(AdicionarAoCarrinho::class, [
            'productId' => $produto->id,
            'maxStock'  => $produto->stock,
        ])
        ->call('adicionar')
        ->assertSet('adicionado', true)
        ->assertSet('erro', '');

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $produto->id,
        ]);
    });

    it('carrinho Livewire exibe itens adicionados', function (): void {
        $tenant  = criarTenantStorefront('loja-carrinho-lw');
        $produto = criarProdutoStorefront($tenant, 'prod-cart-lw');
        $user    = loginComo('customer');

        // Adiciona item ao carrinho via API para ter dados reais
        $this->actingAs($user)->postJson('/api/v1/cart/items', [
            'product_id' => $produto->id,
            'quantity'   => 2,
        ], [
            'X-Tenant-ID'    => $tenant->id,
            'X-Cart-Session' => 'sf-cart-session-lw',
        ]);

        // Simula contexto da vitrine
        app()->instance('storefront.tenant', $tenant);
        session(['cart_session_id' => 'sf-cart-session-lw']);

        Livewire::actingAs($user)
            ->test(CarrinhoPage::class)
            ->assertSee($produto->name);
    });

});
