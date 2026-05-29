<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Livewire;

use App\Modules\Orders\Application\UseCases\Checkout\CheckoutCommand;
use App\Modules\Orders\Application\UseCases\Checkout\CheckoutHandler;
use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Shipping\Application\UseCases\CalculateShipping\CalculateShippingCommand;
use App\Modules\Shipping\Application\UseCases\CalculateShipping\CalculateShippingHandler;
use App\Modules\Shipping\Domain\ValueObjects\ShippingAddress;
use App\Modules\Shipping\Domain\ValueObjects\ShippingOption;
use App\Support\StorefrontContext;
use Livewire\Component;

final class CheckoutForm extends Component
{
    public int $passo = 1;

    // Passo 1 — Endereço
    public string $nomeDestinatario = '';
    public string $cep              = '';
    public string $endereco         = '';
    public string $numero           = '';
    public string $complemento      = '';
    public string $cidade           = '';
    public string $estado           = '';

    // Passo 2 — Frete
    public array  $opcoesFreteRaw  = [];
    public string $freteEscolhido  = '';

    // Passo 3 — Pagamento
    public string $metodoPagamento = 'pix';
    public string $notas           = '';

    // Subtotal do carrinho (para calcular frete)
    public int $subtotalCentavos = 0;

    public string $erro = '';

    public function mount(GetCartHandler $handler): void
    {
        $cart = $handler->handle(
            StorefrontContext::tenantId(),
            StorefrontContext::sessionId(),
            auth()->id(),
        );
        $this->subtotalCentavos = $cart->subtotal()->centavos();

        // Pré-preenche nome do destinatário com o do usuário autenticado
        if (auth()->check()) {
            $this->nomeDestinatario = auth()->user()->name;
        }
    }

    // ─── Passo 1 → 2 ─────────────────────────────────────────────────────────

    protected function regrasEndereco(): array
    {
        return [
            'nomeDestinatario' => ['required', 'string', 'max:150'],
            'cep'              => ['required', 'string', 'min:8', 'max:9'],
            'endereco'         => ['required', 'string', 'max:200'],
            'numero'           => ['required', 'string', 'max:20'],
            'complemento'      => ['nullable', 'string', 'max:100'],
            'cidade'           => ['required', 'string', 'max:100'],
            'estado'           => ['required', 'string', 'size:2'],
        ];
    }

    public function proximoPasso(CalculateShippingHandler $shippingHandler): void
    {
        $this->erro = '';

        if ($this->passo === 1) {
            $this->validate($this->regrasEndereco());
            $this->calcularFrete($shippingHandler);
            $this->passo = 2;
            return;
        }

        if ($this->passo === 2) {
            if (empty($this->freteEscolhido)) {
                $this->erro = 'Selecione uma opção de frete.';
                return;
            }
            $this->passo = 3;
            return;
        }
    }

    public function voltarPasso(): void
    {
        if ($this->passo > 1) {
            $this->passo--;
        }
    }

    // ─── Passo 3 → Finalizar ─────────────────────────────────────────────────

    public function finalizar(CheckoutHandler $checkoutHandler): void
    {
        $this->erro = '';

        if (! auth()->check()) {
            $this->redirectRoute('loja.login', request()->route('tenantSlug'));
            return;
        }

        try {
            $freteOpcao = $this->resolverOpcaoFrete();

            $enderecoEntrega = new ShippingAddress(
                recipientName: $this->nomeDestinatario,
                zipcode:       $this->cep,
                address:       $this->endereco,
                number:        $this->numero,
                complement:    $this->complemento ?: null,
                city:          $this->cidade,
                state:         mb_strtoupper($this->estado),
            );

            $order = $checkoutHandler->handle(new CheckoutCommand(
                tenantId:           StorefrontContext::tenantId(),
                sessionId:          StorefrontContext::sessionId(),
                userId:             auth()->id(),
                paymentMethod:      $this->metodoPagamento,
                notes:              $this->notas ?: null,
                shippingOptionId:   $freteOpcao?->id,
                shippingAddress:    $enderecoEntrega,
                shippingServiceCode: $freteOpcao?->serviceCode,
            ));

            $tenantSlug = request()->route('tenantSlug');
            $this->redirectRoute('loja.confirmacao', [
                'tenantSlug' => $tenantSlug,
                'orderId'    => $order->id(),
            ]);
        } catch (\Throwable $e) {
            $this->erro = $e->getMessage();
        }
    }

    private function calcularFrete(CalculateShippingHandler $handler): void
    {
        $cepLimpo = preg_replace('/\D/', '', $this->cep);

        try {
            $opcoes = $handler->handle(new CalculateShippingCommand(
                tenantId:          StorefrontContext::tenantId(),
                toZipcode:         $cepLimpo,
                toState:           mb_strtoupper($this->estado),
                sessionId:         StorefrontContext::sessionId(),
                userId:            auth()->id(),
                subtotalCentavos:  $this->subtotalCentavos,
            ));

            $this->opcoesFreteRaw = array_map(fn (ShippingOption $o) => [
                'id'             => $o->id,
                'nome'           => $o->name,
                'transportadora' => $o->carrier,
                'preco'          => $o->priceCentavos,
                'minDias'        => $o->minDays,
                'maxDias'        => $o->maxDays,
                'gratis'         => $o->isFreeShipping,
                'serviceCode'    => $o->serviceCode,
            ], $opcoes);

            // Pré-seleciona opção mais barata
            if (! empty($this->opcoesFreteRaw)) {
                $this->freteEscolhido = $this->opcoesFreteRaw[0]['id'];
            }
        } catch (\Throwable $e) {
            $this->opcoesFreteRaw = [];
        }
    }

    private function resolverOpcaoFrete(): ?ShippingOption
    {
        $raw = collect($this->opcoesFreteRaw)->firstWhere('id', $this->freteEscolhido);

        if (! $raw) {
            return null;
        }

        return new ShippingOption(
            id:             $raw['id'],
            name:           $raw['nome'],
            carrier:        $raw['transportadora'],
            serviceCode:    $raw['serviceCode'],
            priceCentavos:  $raw['preco'],
            minDays:        $raw['minDias'],
            maxDays:        $raw['maxDias'],
            isFreeShipping: $raw['gratis'],
        );
    }

    public function render()
    {
        return view('storefront.livewire.checkout-form');
    }
}
