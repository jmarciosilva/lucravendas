<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Controllers;

use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Support\StorefrontContext;
use Illuminate\View\View;

final class CarrinhoController
{
    public function __construct(
        private readonly GetCartHandler $getCartHandler,
    ) {}

    public function __invoke(): View
    {
        $tenantId  = StorefrontContext::tenantId();
        $sessionId = StorefrontContext::sessionId();
        $userId    = auth()->id();

        $carrinho = $this->getCartHandler->handle($tenantId, $sessionId, $userId);

        return view('storefront.carrinho.index', compact('carrinho'));
    }
}
