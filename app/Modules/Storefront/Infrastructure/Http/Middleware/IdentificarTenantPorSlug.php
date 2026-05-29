<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Infrastructure\Http\Middleware;

use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Identifica o tenant a partir do slug na URL (/loja/{tenantSlug}/).
 *
 * Além de vincular o TenantModel ao container, resolve o tema visual do tenant
 * e faz prepend no ViewFinder — views do tema têm prioridade sobre as views
 * base, com fallback automático para o tema genérico (views existentes).
 *
 * Resolução de tema:
 *   resources/views/storefront/themes/{tema}/storefront/home.blade.php  ← tema
 *   resources/views/storefront/home.blade.php                           ← fallback
 */
final class IdentificarTenantPorSlug
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug   = $request->route('tenantSlug');
        $tenant = TenantModel::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (! $tenant) {
            abort(404, 'Loja não encontrada.');
        }

        // Disponibiliza o tenant para controllers e Livewire via container
        app()->instance('storefront.tenant', $tenant);

        // Compartilha com todas as views da requisição
        View::share('lojaAtual', $tenant);

        // Prepend do tema visual — apenas quando diferente do genérico
        $this->resolverTema($tenant->theme());

        return $next($request);
    }

    private function resolverTema(string $theme): void
    {
        if ($theme === 'generico') {
            return;
        }

        $themePath = resource_path("views/storefront/themes/{$theme}");

        if (is_dir($themePath)) {
            // Prepend faz o ViewFinder checar o diretório do tema ANTES das views base.
            // Para view 'storefront.home', procura primeiro em:
            //   {themePath}/storefront/home.blade.php
            // e cai no fallback:
            //   resources/views/storefront/home.blade.php
            view()->getFinder()->prependLocation($themePath);
            // Flush limpa resoluções cacheadas em memória (necessário em testes e Octane)
            view()->getFinder()->flush();
        }
    }
}
