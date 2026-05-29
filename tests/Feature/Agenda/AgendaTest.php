<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Agenda\Infrastructure\Models\AgendaItemModel;
use App\Modules\Agenda\Infrastructure\Models\AgendaRegistrationModel;
use App\Modules\Marketplace\Infrastructure\Models\SellerModel;
use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Illuminate\Support\Facades\DB;

/**
 * Cria um tenant de teste para a Fase 13 via DB::table (workaround stancl/tenancy).
 *
 * @param  string $slug    Slug único do tenant
 * @param  array  $data    Dados extras do tenant (features, etc.)
 * @return TenantModel
 */
function criarTenantAgenda(string $slug, array $data = []): TenantModel
{
    $id = 'tenant-ag-' . $slug;

    DB::table('tenants')->insert([
        'id'         => $id,
        'name'       => 'Loja Agenda ' . $slug,
        'slug'       => $slug . '-ag',
        'plan'       => 'free',
        'status'     => 'active',
        'profile'    => 'esoterismo',
        'data'       => json_encode($data),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return TenantModel::find($id);
}

describe('FASE 13 — Agenda de Eventos e Cursos', function (): void {

    /**
     * Teste 1: evento publicado aparece na listagem pública.
     * Verifica que a API retorna eventos com status=published e starts_at futuro.
     */
    it('evento publicado aparece na listagem pública', function (): void {
        $tenant = criarTenantAgenda('lista-pub');

        // Cria um evento publicado e futuro
        AgendaItemModel::create([
            'tenant_id'      => $tenant->id,
            'type'           => 'evento',
            'title'          => 'Evento Público Teste',
            'slug'           => 'evento-publico-teste',
            'starts_at'      => now()->addDays(3),
            'ends_at'        => now()->addDays(3)->addHours(2),
            'price_centavos' => 0,
            'status'         => 'published',
            'slots_used'     => 0,
        ]);

        // Cria um evento em rascunho (não deve aparecer)
        AgendaItemModel::create([
            'tenant_id'      => $tenant->id,
            'type'           => 'curso',
            'title'          => 'Curso Rascunho',
            'slug'           => 'curso-rascunho-ag',
            'starts_at'      => now()->addDays(5),
            'ends_at'        => now()->addDays(5)->addHours(4),
            'price_centavos' => 0,
            'status'         => 'draft',
            'slots_used'     => 0,
        ]);

        $this->getJson('/api/v1/agenda', ['X-Tenant-ID' => $tenant->id])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Evento Público Teste')
            ->assertJsonPath('data.0.status', 'published');
    });

    /**
     * Teste 2: inscrição gratuita confirma imediatamente.
     * Verifica que eventos gratuitos têm status=confirmed logo após a inscrição.
     */
    it('inscrição gratuita confirma imediatamente', function (): void {
        $tenant = criarTenantAgenda('inscricao-gratis');

        // Cria usuário para autenticação
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        // Cria evento gratuito publicado com vagas
        $item = AgendaItemModel::create([
            'tenant_id'      => $tenant->id,
            'type'           => 'workshop',
            'title'          => 'Workshop Gratuito',
            'slug'           => 'workshop-gratuito-ag',
            'starts_at'      => now()->addWeek(),
            'ends_at'        => now()->addWeek()->addHours(3),
            'price_centavos' => 0,
            'status'         => 'published',
            'slots'          => 10,
            'slots_used'     => 0,
        ]);

        // Realiza inscrição como usuário autenticado
        $this->actingAs($user)
            ->postJson(
                '/api/v1/agenda/' . $item->id . '/register',
                [],
                ['X-Tenant-ID' => $tenant->id]
            )
            ->assertCreated()
            ->assertJsonPath('data.status', 'confirmed');

        // Verifica que a inscrição foi criada com confirmed_at preenchido
        $registration = AgendaRegistrationModel::where('agenda_item_id', $item->id)
            ->where('user_id', $user->id)
            ->first();

        expect($registration)->not->toBeNull()
            ->and($registration->status)->toBe('confirmed')
            ->and($registration->confirmed_at)->not->toBeNull();

        // Verifica que slots_used foi incrementado
        $item->refresh();
        expect($item->slots_used)->toBe(1);
    });

    /**
     * Teste 3: evento com vagas esgotadas retorna 422.
     * Verifica que a API impede inscrição quando todas as vagas estão ocupadas.
     */
    it('evento com vagas esgotadas retorna 422', function (): void {
        $tenant = criarTenantAgenda('vagas-esgotadas');
        $user   = User::factory()->create(['tenant_id' => $tenant->id]);

        // Cria evento com 1 vaga já preenchida (esgotado)
        $item = AgendaItemModel::create([
            'tenant_id'      => $tenant->id,
            'type'           => 'curso',
            'title'          => 'Curso Lotado',
            'slug'           => 'curso-lotado-ag',
            'starts_at'      => now()->addDays(10),
            'ends_at'        => now()->addDays(10)->addHours(8),
            'price_centavos' => 5000,
            'status'         => 'published',
            'slots'          => 1,
            'slots_used'     => 1, // vaga única já ocupada
        ]);

        // Tenta se inscrever — deve retornar 422 com mensagem de vagas esgotadas
        $this->actingAs($user)
            ->postJson(
                '/api/v1/agenda/' . $item->id . '/register',
                [],
                ['X-Tenant-ID' => $tenant->id]
            )
            ->assertStatus(422)
            ->assertJsonPath('message', 'Vagas esgotadas.');
    });

    /**
     * Teste 4: evento de seller NÃO aparece quando feature seller_events_on_marketplace=false.
     * Verifica que a feature flag filtra corretamente os eventos de sellers.
     */
    it('evento de seller não aparece quando feature seller_events_on_marketplace=false', function (): void {
        // Cria tenant COM a feature desabilitada explicitamente
        $tenant = criarTenantAgenda('sem-seller-events', [
            'features' => ['seller_events_on_marketplace' => false],
        ]);

        // Cria um seller vinculado ao tenant
        $seller = SellerModel::create([
            'name'            => 'Seller da Agenda',
            'slug'            => 'seller-agenda-' . uniqid(),
            'tenant_id'       => $tenant->id,
            'status'          => 'active',
            'commission_rate' => 10,
        ]);

        // Cria evento do seller (deve ser filtrado)
        AgendaItemModel::create([
            'tenant_id'      => $tenant->id,
            'seller_id'      => $seller->id,
            'type'           => 'evento',
            'title'          => 'Evento do Seller',
            'slug'           => 'evento-seller-ag',
            'starts_at'      => now()->addDays(2),
            'ends_at'        => now()->addDays(2)->addHours(2),
            'price_centavos' => 0,
            'status'         => 'published',
            'slots_used'     => 0,
        ]);

        // Cria evento do próprio tenant (deve aparecer)
        AgendaItemModel::create([
            'tenant_id'      => $tenant->id,
            'seller_id'      => null,
            'type'           => 'evento',
            'title'          => 'Evento do Tenant',
            'slug'           => 'evento-tenant-ag',
            'starts_at'      => now()->addDays(2),
            'ends_at'        => now()->addDays(2)->addHours(2),
            'price_centavos' => 0,
            'status'         => 'published',
            'slots_used'     => 0,
        ]);

        // Deve retornar apenas o evento do tenant (sem seller)
        $this->getJson('/api/v1/agenda', ['X-Tenant-ID' => $tenant->id])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Evento do Tenant');
    });

    /**
     * Teste 5: evento de seller APARECE quando feature seller_events_on_marketplace=true.
     * Verifica que com a feature habilitada, eventos de sellers são listados.
     */
    it('evento de seller aparece quando feature seller_events_on_marketplace=true', function (): void {
        // Cria tenant COM a feature habilitada explicitamente
        $tenant = criarTenantAgenda('com-seller-events', [
            'features' => ['seller_events_on_marketplace' => true],
        ]);

        // Cria seller vinculado ao tenant
        $seller = SellerModel::create([
            'name'            => 'Seller Eventos Plus',
            'slug'            => 'seller-eventos-plus-' . uniqid(),
            'tenant_id'       => $tenant->id,
            'status'          => 'active',
            'commission_rate' => 10,
        ]);

        // Cria evento do seller (deve aparecer quando feature=true)
        AgendaItemModel::create([
            'tenant_id'      => $tenant->id,
            'seller_id'      => $seller->id,
            'type'           => 'workshop',
            'title'          => 'Workshop do Seller Plus',
            'slug'           => 'workshop-seller-plus-ag',
            'starts_at'      => now()->addDays(4),
            'ends_at'        => now()->addDays(4)->addHours(2),
            'price_centavos' => 10000,
            'status'         => 'published',
            'slots_used'     => 0,
        ]);

        // Deve retornar o evento do seller também
        $this->getJson('/api/v1/agenda', ['X-Tenant-ID' => $tenant->id])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Workshop do Seller Plus');
    });

});
