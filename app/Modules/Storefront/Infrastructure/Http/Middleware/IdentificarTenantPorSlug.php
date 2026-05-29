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
 * Vincula o TenantModel ao container de IoC e compartilha com todas as views,
 * permitindo que controllers e Livewire components acessem o tenant sem
 * precisar resolver novamente.
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

        return $next($request);
    }
}
