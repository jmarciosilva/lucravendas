<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inicializa o tenant a partir do header X-Tenant-ID.
 *
 * Necessário para clientes mobile e integrações que não operam via subdomínio.
 * Encapsula a identificação por header numa classe explícita para rastreabilidade.
 */
final class InitializeTenancyByHeader extends InitializeTenancyByRequestData
{
    /**
     * Header que transporta o identificador do tenant nas requisições mobile.
     * Usar header em vez de query string evita vazamento de IDs em logs de URL.
     */
    protected string $header = 'X-Tenant-ID';

    public function handle(Request $request, Closure $next): Response
    {
        return parent::handle($request, $next);
    }
}
