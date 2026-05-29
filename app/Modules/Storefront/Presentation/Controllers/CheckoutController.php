<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Controllers;

use App\Modules\Orders\Infrastructure\Models\OrderModel;
use App\Support\StorefrontContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class CheckoutController
{
    public function index(): View|RedirectResponse
    {
        $tenantId  = StorefrontContext::tenantId();
        $sessionId = StorefrontContext::sessionId();

        // Redireciona para o carrinho se vazio (verificação rápida via sessão)
        return view('storefront.checkout.index', compact('tenantId', 'sessionId'));
    }

    public function confirmacao(string $tenantSlug, int $orderId): View
    {
        $tenantId = StorefrontContext::tenantId();

        $pedido = OrderModel::with(['items', 'statusHistory'])
            ->where('id', $orderId)
            ->where('tenant_id', $tenantId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Busca a última transação de pagamento do pedido
        $transacao = $pedido->transactions()->latest()->first();

        return view('storefront.checkout.confirmacao', compact('pedido', 'transacao'));
    }
}
