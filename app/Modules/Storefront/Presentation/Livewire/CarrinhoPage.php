<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Livewire;

use App\Modules\Orders\Application\UseCases\ApplyCoupon\ApplyCouponCommand;
use App\Modules\Orders\Application\UseCases\ApplyCoupon\ApplyCouponHandler;
use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Orders\Application\UseCases\RemoveCartItem\RemoveCartItemHandler;
use App\Modules\Orders\Application\UseCases\RemoveCoupon\RemoveCouponHandler;
use App\Modules\Orders\Application\UseCases\UpdateCartItem\UpdateCartItemCommand;
use App\Modules\Orders\Application\UseCases\UpdateCartItem\UpdateCartItemHandler;
use App\Modules\Orders\Domain\Entities\Cart;
use App\Support\StorefrontContext;
use Livewire\Attributes\On;
use Livewire\Component;

final class CarrinhoPage extends Component
{
    public array   $itens            = [];
    public int     $subtotalCentavos = 0;
    public string  $codigoCupom      = '';
    public string  $erroCupom        = '';
    public string  $mensagemCupom    = '';
    public bool    $temCupom         = false;
    public int     $descontoCentavos = 0;
    public string  $erro             = '';
    public string  $tenantSlug       = '';

    public function mount(GetCartHandler $handler): void
    {
        $this->tenantSlug = \App\Support\StorefrontContext::tenant()->slug;
        $this->sincronizar($handler);
    }

    #[On('carrinho-atualizado')]
    public function atualizar(GetCartHandler $handler): void
    {
        $this->sincronizar($handler);
    }

    public function atualizarQuantidade(UpdateCartItemHandler $handler, int $itemId, int $quantidade): void
    {
        $this->erro = '';
        try {
            $cart = $handler->handle(new UpdateCartItemCommand(
                tenantId:  StorefrontContext::tenantId(),
                sessionId: StorefrontContext::sessionId(),
                userId:    auth()->id(),
                itemId:    $itemId,
                quantity:  $quantidade,
            ));
            $this->mapearItens($cart);
            $this->dispatch('carrinho-atualizado');
        } catch (\Throwable $e) {
            $this->erro = $e->getMessage();
        }
    }

    public function removerItem(RemoveCartItemHandler $handler, int $itemId): void
    {
        $this->erro = '';
        try {
            $cart = $handler->handle(
                StorefrontContext::tenantId(),
                StorefrontContext::sessionId(),
                auth()->id(),
                $itemId,
            );
            $this->mapearItens($cart);
            $this->dispatch('carrinho-atualizado');
        } catch (\Throwable $e) {
            $this->erro = $e->getMessage();
        }
    }

    public function aplicarCupom(ApplyCouponHandler $handler): void
    {
        $this->erroCupom    = '';
        $this->mensagemCupom = '';

        try {
            $cart = $handler->handle(new ApplyCouponCommand(
                tenantId:   StorefrontContext::tenantId(),
                sessionId:  StorefrontContext::sessionId(),
                userId:     auth()->id(),
                couponCode: $this->codigoCupom,
            ));
            $this->mapearItens($cart);
            $this->mensagemCupom = 'Cupom aplicado com sucesso!';
        } catch (\Throwable $e) {
            $this->erroCupom = $e->getMessage();
        }
    }

    public function removerCupom(RemoveCouponHandler $handler): void
    {
        try {
            $cart = $handler->handle(
                StorefrontContext::tenantId(),
                StorefrontContext::sessionId(),
                auth()->id(),
            );
            $this->codigoCupom   = '';
            $this->mensagemCupom = '';
            $this->mapearItens($cart);
        } catch (\Throwable $e) {
            $this->erro = $e->getMessage();
        }
    }

    private function sincronizar(GetCartHandler $handler): void
    {
        try {
            $cart = $handler->handle(
                StorefrontContext::tenantId(),
                StorefrontContext::sessionId(),
                auth()->id(),
            );
            $this->mapearItens($cart);
        } catch (\Throwable $e) {
            $this->erro = $e->getMessage();
        }
    }

    private function mapearItens(Cart $cart): void
    {
        $this->subtotalCentavos = $cart->subtotal()->centavos();
        $this->temCupom         = $cart->couponId() !== null;

        $this->itens = array_map(fn ($item) => [
            'id'         => $item->id(),
            'nome'       => $item->productName(),
            'quantidade' => $item->quantity(),
            'unitario'   => $item->unitPrice()->centavos(),
            'total'      => $item->total()->centavos(),
        ], $cart->items());
    }

    public function render()
    {
        return view('storefront.livewire.carrinho-page');
    }
}
