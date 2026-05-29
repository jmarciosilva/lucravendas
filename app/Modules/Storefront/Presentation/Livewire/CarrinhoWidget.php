<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Livewire;

use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Orders\Domain\Entities\Cart;
use App\Support\StorefrontContext;
use Livewire\Attributes\On;
use Livewire\Component;

final class CarrinhoWidget extends Component
{
    public int    $totalItens = 0;
    public int    $subtotalCentavos = 0;
    public array  $previewItens = [];
    public bool   $aberto = false;

    public function mount(GetCartHandler $handler): void
    {
        $this->carregarCarrinho($handler);
    }

    #[On('carrinho-atualizado')]
    public function atualizar(GetCartHandler $handler): void
    {
        $this->carregarCarrinho($handler);
    }

    public function toggleAberto(): void
    {
        $this->aberto = ! $this->aberto;
    }

    private function carregarCarrinho(GetCartHandler $handler): void
    {
        try {
            $cart = $handler->handle(
                StorefrontContext::tenantId(),
                StorefrontContext::sessionId(),
                auth()->id(),
            );

            $this->totalItens       = count($cart->items());
            $this->subtotalCentavos = $cart->subtotal()->centavos();

            // Prévia dos 3 primeiros itens para o dropdown
            $this->previewItens = array_map(
                fn ($item) => [
                    'id'        => $item->id(),
                    'nome'      => $item->productName(),
                    'quantidade'=> $item->quantity(),
                    'preco'     => $item->total()->centavos(),
                ],
                array_slice($cart->items(), 0, 3),
            );
        } catch (\Throwable) {
            $this->totalItens = 0;
        }
    }

    public function render()
    {
        return view('storefront.livewire.carrinho-widget');
    }
}
