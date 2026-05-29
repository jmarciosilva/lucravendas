<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Livewire;

use App\Modules\Orders\Application\UseCases\AddCartItem\AddCartItemCommand;
use App\Modules\Orders\Application\UseCases\AddCartItem\AddCartItemHandler;
use App\Support\StorefrontContext;
use Livewire\Component;

final class AdicionarAoCarrinho extends Component
{
    public int    $productId;
    public int    $quantity   = 1;
    public ?int   $variantId  = null;
    public int    $maxStock   = 99;
    public bool   $adicionado = false;
    public string $erro       = '';
    public string $tenantSlug = '';

    public function mount(int $productId, int $maxStock = 99, ?int $variantId = null): void
    {
        $this->productId  = $productId;
        $this->maxStock   = $maxStock;
        $this->variantId  = $variantId;
        $this->tenantSlug = \App\Support\StorefrontContext::tenant()->slug;
    }

    public function adicionar(AddCartItemHandler $handler): void
    {
        $this->erro = '';

        if ($this->quantity < 1 || $this->quantity > $this->maxStock) {
            $this->erro = 'Quantidade inválida.';
            return;
        }

        try {
            $handler->handle(new AddCartItemCommand(
                tenantId:  StorefrontContext::tenantId(),
                sessionId: StorefrontContext::sessionId(),
                userId:    auth()->id(),
                productId: $this->productId,
                variantId: $this->variantId,
                quantity:  $this->quantity,
            ));

            $this->adicionado = true;
            $this->dispatch('carrinho-atualizado');
        } catch (\Throwable $e) {
            $this->erro = $e->getMessage();
        }
    }

    public function render()
    {
        return view('storefront.livewire.adicionar-ao-carrinho');
    }
}
