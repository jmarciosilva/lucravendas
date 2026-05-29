<?php

declare(strict_types=1);

namespace App\Support;

use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Illuminate\Support\Str;

/**
 * Helpers estáticos para acessar o contexto da vitrine na requisição atual.
 *
 * Centraliza o acesso ao tenant identificado pelo middleware e ao ID de sessão
 * do carrinho anônimo, evitando repetição nos controllers e Livewire components.
 */
final class StorefrontContext
{
    /** Retorna o TenantModel da loja atual (vinculado pelo middleware) */
    public static function tenant(): TenantModel
    {
        return app('storefront.tenant');
    }

    /** Retorna o ID do tenant (UUID string) */
    public static function tenantId(): string
    {
        return self::tenant()->id;
    }

    /**
     * Retorna (ou cria) o UUID de sessão do carrinho anônimo.
     * Persiste na sessão PHP para identificar o carrinho entre requisições.
     */
    public static function sessionId(): string
    {
        if (! session()->has('cart_session_id')) {
            session(['cart_session_id' => (string) Str::uuid()]);
        }

        return session('cart_session_id');
    }
}
