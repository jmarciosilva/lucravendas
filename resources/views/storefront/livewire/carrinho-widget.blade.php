<div class="relative" x-data="{ aberto: false }">

    {{-- Botão do carrinho --}}
    <button @click="aberto = !aberto"
            class="relative flex items-center gap-1.5 text-gray-700 hover:text-emerald-600 transition-colors p-1">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        @if($totalItens > 0)
        <span class="absolute -top-1 -right-1 bg-emerald-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">
            {{ $totalItens > 9 ? '9+' : $totalItens }}
        </span>
        @endif
    </button>

    {{-- Dropdown mini-carrinho --}}
    <div x-show="aberto"
         @click.away="aberto = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-lg border border-gray-100 z-50 overflow-hidden">

        <div class="p-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Carrinho</h3>
        </div>

        @if(empty($previewItens) && $totalItens === 0)
        <div class="p-6 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-sm">Carrinho vazio</p>
        </div>
        @else
        <div class="divide-y divide-gray-50 max-h-60 overflow-y-auto">
            @foreach($previewItens as $item)
            <div class="px-4 py-3 flex justify-between items-start">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $item['nome'] }}</p>
                    <p class="text-xs text-gray-500">Qtd: {{ $item['quantidade'] }}</p>
                </div>
                <p class="text-sm font-semibold text-gray-900 ml-4 flex-shrink-0">
                    R$ {{ number_format($item['preco'] / 100, 2, ',', '.') }}
                </p>
            </div>
            @endforeach

            @if($totalItens > count($previewItens))
            <div class="px-4 py-2 text-xs text-gray-400 text-center">
                + {{ $totalItens - count($previewItens) }} item(ns) adicional(is)
            </div>
            @endif
        </div>

        <div class="p-4 border-t border-gray-100">
            <div class="flex justify-between text-sm font-semibold text-gray-800 mb-3">
                <span>Subtotal</span>
                <span>R$ {{ number_format($subtotalCentavos / 100, 2, ',', '.') }}</span>
            </div>
            <a href="{{ route('loja.carrinho', request()->route('tenantSlug')) }}"
               @click="aberto = false"
               class="block w-full text-center bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-emerald-700 transition-colors">
                Ver carrinho
            </a>
        </div>
        @endif
    </div>
</div>
