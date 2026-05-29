<div>
    @if($adicionado)
    <div class="flex flex-col gap-3">
        <div class="flex items-center gap-2 text-emerald-600 text-sm font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Adicionado ao carrinho!
        </div>
        <div class="flex gap-3">
            <button wire:click="$set('adicionado', false)"
                    class="flex-1 border border-gray-300 text-gray-700 text-sm font-medium py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                Continuar comprando
            </button>
            <a href="{{ route('loja.carrinho', $tenantSlug) }}"
               class="flex-1 text-center bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-emerald-700 transition-colors">
                Ver carrinho
            </a>
        </div>
    </div>
    @else

    @if($erro)
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm mb-3">
        {{ $erro }}
    </div>
    @endif

    <div class="flex items-center gap-3 mb-4">
        <label class="text-sm font-medium text-gray-700">Quantidade:</label>
        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
            <button wire:click="$set('quantity', max(1, quantity - 1))"
                    class="px-3 py-2 text-gray-600 hover:bg-gray-100 transition-colors text-lg leading-none">
                −
            </button>
            <span class="px-4 py-2 text-sm font-semibold text-gray-900 min-w-[3rem] text-center">
                {{ $quantity }}
            </span>
            <button wire:click="$set('quantity', min(maxStock, quantity + 1))"
                    class="px-3 py-2 text-gray-600 hover:bg-gray-100 transition-colors text-lg leading-none">
                +
            </button>
        </div>
    </div>

    <button wire:click="adicionar"
            wire:loading.attr="disabled"
            wire:loading.class="opacity-75 cursor-not-allowed"
            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3.5 px-6 rounded-xl transition-colors flex items-center justify-center gap-2">
        <span wire:loading.remove>
            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Adicionar ao carrinho
        </span>
        <span wire:loading>Adicionando...</span>
    </button>
    @endif
</div>
