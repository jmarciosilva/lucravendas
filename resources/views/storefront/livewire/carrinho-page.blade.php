<div>
    @if($erro)
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm mb-6">
        {{ $erro }}
    </div>
    @endif

    @if(empty($itens))
    <div class="text-center py-20 text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <p class="text-lg font-medium">Seu carrinho está vazio</p>
        <a href="{{ route('loja.produtos.index', $tenantSlug) }}"
           class="mt-4 inline-block text-emerald-600 hover:underline text-sm">
            Ver produtos
        </a>
    </div>
    @else

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Lista de itens --}}
        <div class="flex-1 space-y-4">
            @foreach($itens as $item)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ $item['nome'] }}</h3>
                        <p class="text-sm text-gray-500 mt-0.5">
                            R$ {{ number_format($item['unitario'] / 100, 2, ',', '.') }} / unidade
                        </p>
                    </div>
                    <p class="font-bold text-gray-900 text-lg flex-shrink-0">
                        R$ {{ number_format($item['total'] / 100, 2, ',', '.') }}
                    </p>
                </div>

                <div class="flex items-center justify-between mt-4">
                    {{-- Controle de quantidade --}}
                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                        <button wire:click="atualizarQuantidade({{ $item['id'] }}, {{ max(0, $item['quantidade'] - 1) }})"
                                class="px-3 py-1.5 text-gray-500 hover:bg-gray-100 transition-colors text-lg leading-none">
                            −
                        </button>
                        <span class="px-4 py-1.5 text-sm font-semibold text-gray-900 min-w-[2.5rem] text-center">
                            {{ $item['quantidade'] }}
                        </span>
                        <button wire:click="atualizarQuantidade({{ $item['id'] }}, {{ $item['quantidade'] + 1 }})"
                                class="px-3 py-1.5 text-gray-500 hover:bg-gray-100 transition-colors text-lg leading-none">
                            +
                        </button>
                    </div>

                    {{-- Remover --}}
                    <button wire:click="removerItem({{ $item['id'] }})"
                            wire:confirm="Remover este item do carrinho?"
                            class="text-xs text-red-500 hover:text-red-700 hover:underline transition-colors">
                        Remover
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Resumo / sidebar --}}
        <div class="lg:w-80 space-y-4">

            {{-- Cupom --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Cupom de desconto</h3>

                @if($mensagemCupom)
                <p class="text-sm text-emerald-600 mb-3">{{ $mensagemCupom }}</p>
                @endif
                @if($erroCupom)
                <p class="text-sm text-red-600 mb-3">{{ $erroCupom }}</p>
                @endif

                @if($temCupom)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-emerald-600 font-medium">✓ Cupom aplicado</span>
                    <button wire:click="removerCupom"
                            class="text-xs text-red-500 hover:underline">
                        Remover
                    </button>
                </div>
                @else
                <div class="flex gap-2">
                    <input type="text"
                           wire:model="codigoCupom"
                           placeholder="Código do cupom"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 uppercase"
                           style="text-transform: uppercase">
                    <button wire:click="aplicarCupom"
                            class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                        Aplicar
                    </button>
                </div>
                @endif
            </div>

            {{-- Totais --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-800 mb-4">Resumo</h3>

                <div class="space-y-2 text-sm mb-5">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal ({{ count($itens) }} item(ns))</span>
                        <span>R$ {{ number_format($subtotalCentavos / 100, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-gray-900 text-base pt-2 border-t border-gray-100">
                        <span>Total</span>
                        <span>R$ {{ number_format($subtotalCentavos / 100, 2, ',', '.') }}</span>
                    </div>
                </div>

                <a href="{{ route('loja.checkout', $tenantSlug) }}"
                   class="block w-full text-center bg-emerald-600 text-white font-semibold py-3 rounded-xl hover:bg-emerald-700 transition-colors">
                    Finalizar pedido
                </a>

                <a href="{{ route('loja.produtos.index', $tenantSlug) }}"
                   class="block w-full text-center text-gray-500 hover:text-gray-700 text-sm mt-3 transition-colors">
                    Continuar comprando
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
