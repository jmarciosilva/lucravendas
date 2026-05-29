{{-- Partials: card de produto para listagens --}}
<a href="{{ route('loja.produtos.show', [$lojaAtual->slug, $produto->slug]) }}"
   class="group bg-white rounded-xl shadow-sm hover:shadow-md border border-gray-100 overflow-hidden transition-all flex flex-col">

    {{-- Imagem --}}
    <div class="aspect-square bg-gray-100 overflow-hidden">
        @php $imagem = $produto->getFirstMediaUrl('images', 'thumb'); @endphp
        @if($imagem)
            <img src="{{ $imagem }}"
                 alt="{{ $produto->name }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-300">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif
    </div>

    {{-- Informações --}}
    <div class="p-4 flex flex-col flex-1">
        <h3 class="text-sm font-semibold text-gray-800 group-hover:text-emerald-600 transition-colors line-clamp-2 mb-2">
            {{ $produto->name }}
        </h3>

        <div class="mt-auto">
            @if($produto->compare_price && $produto->compare_price > $produto->price)
                <p class="text-xs text-gray-400 line-through mb-0.5">
                    R$ {{ number_format($produto->compare_price / 100, 2, ',', '.') }}
                </p>
            @endif
            <p class="text-lg font-bold text-emerald-600">
                R$ {{ number_format($produto->price / 100, 2, ',', '.') }}
            </p>

            @if($produto->stock === 0)
                <span class="inline-block mt-2 text-xs text-red-500 font-medium">Sem estoque</span>
            @endif
        </div>
    </div>
</a>
