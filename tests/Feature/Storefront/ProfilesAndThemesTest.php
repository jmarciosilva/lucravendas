<?php

declare(strict_types=1);

use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Illuminate\Support\Facades\DB;

// Cria tenant diretamente no DB para contornar a serialização do stancl/tenancy
function criarTenantComPerfil(string $slug, string $profile = 'generico', array $data = []): TenantModel
{
    $id = 'tenant-p12-' . $slug;
    DB::table('tenants')->insert([
        'id'         => $id,
        'name'       => 'Loja ' . $slug,
        'slug'       => $slug,
        'plan'       => 'free',
        'status'     => 'active',
        'profile'    => $profile,
        'data'       => json_encode($data),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return TenantModel::find($id);
}

describe('FASE 12 — Perfis, Feature Flags e Temas', function (): void {

    describe('helper feature()', function (): void {

        it('retorna o padrão do perfil quando tenant não sobrescreve a feature', function (): void {
            $tenant = criarTenantComPerfil('esoterismo-f1', 'esoterismo');

            expect($tenant->feature('agenda'))->toBeTrue()
                ->and($tenant->feature('blog'))->toBeTrue()
                ->and($tenant->feature('marketplace'))->toBeTrue();
        });

        it('retorna false para feature não habilitada no perfil', function (): void {
            $tenant = criarTenantComPerfil('generico-f1', 'generico');

            expect($tenant->feature('agenda'))->toBeFalse()
                ->and($tenant->feature('blog'))->toBeFalse()
                ->and($tenant->feature('marketplace'))->toBeFalse();
        });

        it('sobrescreve o padrão do perfil quando tenant define a feature explicitamente', function (): void {
            // Perfil genérico tem agenda=false por padrão, mas tenant habilita manualmente
            $tenant = criarTenantComPerfil('generico-override', 'generico', [
                'features' => ['agenda' => true],
            ]);

            expect($tenant->feature('agenda'))->toBeTrue();
        });

        it('sobrescreve para false mesmo quando o perfil tem true', function (): void {
            // Perfil esoterismo tem blog=true, mas lojista desabilita
            $tenant = criarTenantComPerfil('esoterismo-override', 'esoterismo', [
                'features' => ['blog' => false],
            ]);

            expect($tenant->feature('blog'))->toBeFalse();
        });

    });

    describe('helper isMarketplace()', function (): void {

        it('retorna true para perfis de marketplace', function (): void {
            $tenant = criarTenantComPerfil('mkt-test', 'esoterismo');
            expect($tenant->isMarketplace())->toBeTrue();
        });

        it('retorna false para perfis de loja individual', function (): void {
            $tenant = criarTenantComPerfil('loja-test', 'loja_roupas');
            expect($tenant->isMarketplace())->toBeFalse();
        });

    });

    describe('helper theme()', function (): void {

        it('retorna o tema padrão do perfil quando tenant não customiza', function (): void {
            $tenant = criarTenantComPerfil('roupas-tema', 'loja_roupas');
            expect($tenant->theme())->toBe('roupas');
        });

        it('retorna tema customizado quando tenant define data.theme', function (): void {
            $tenant = criarTenantComPerfil('roupas-custom', 'loja_roupas', [
                'theme' => 'generico',
            ]);
            expect($tenant->theme())->toBe('generico');
        });

        it('retorna generico como fallback para perfil sem tema mapeado', function (): void {
            $tenant = criarTenantComPerfil('sem-tema', 'generico');
            expect($tenant->theme())->toBe('generico');
        });

    });

    describe('middleware — resolução de tema no ViewFinder', function (): void {

        beforeEach(fn () => test()->withoutVite());

        it('home carrega normalmente com tema generico (sem prepend)', function (): void {
            $tenant = criarTenantComPerfil('tema-generico-mw', 'generico');

            $this->get("/loja/{$tenant->slug}")
                ->assertStatus(200);
        });

        it('home carrega com tema esoterismo usando fallback (diretório do tema sem views)', function (): void {
            // O tema esoterismo tem diretório criado, mas sem views — cai no fallback genérico
            $tenant = criarTenantComPerfil('tema-eso-mw', 'esoterismo');

            $this->get("/loja/{$tenant->slug}")
                ->assertStatus(200);
        });

        it('view do tema tem prioridade sobre a view base quando existe', function (): void {
            // Cria uma view de tema temporária para teste
            $temaDir = resource_path('views/storefront/themes/test-tema/storefront');
            mkdir($temaDir, 0777, true);
            file_put_contents("{$temaDir}/home.blade.php", '@extends("storefront.layouts.loja") @section("content") <p>TEMA-TESTE</p> @endsection');

            $tenant = criarTenantComPerfil('tema-custom-mw', 'generico', ['theme' => 'test-tema']);

            try {
                $this->get("/loja/{$tenant->slug}")
                    ->assertStatus(200)
                    ->assertSee('TEMA-TESTE');
            } finally {
                // Limpa a view de teste
                @unlink("{$temaDir}/home.blade.php");
                @rmdir($temaDir);
                @rmdir(dirname($temaDir));
            }
        });

    });

});
