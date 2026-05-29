<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Controllers;

use App\Modules\Orders\Infrastructure\Models\OrderModel;
use App\Support\StorefrontContext;
use Illuminate\View\View;

final class MinhaContaController
{
    public function index(): View
    {
        return view('storefront.conta.index');
    }

    public function pedidos(): View
    {
        $tenantId = StorefrontContext::tenantId();

        $pedidos = OrderModel::where('user_id', auth()->id())
            ->where('tenant_id', $tenantId)
            ->latest()
            ->paginate(10);

        return view('storefront.conta.pedidos', compact('pedidos'));
    }

    public function pedido(string $tenantSlug, int $id): View
    {
        $tenantId = StorefrontContext::tenantId();

        $pedido = OrderModel::with(['items', 'statusHistory'])
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('storefront.conta.pedido', compact('pedido'));
    }
}
