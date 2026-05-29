@extends('storefront.layouts.loja')

@section('title', $produto->name)
@section('description', Str::limit(strip_tags($produto->description), 160))

@section('og_meta')
    @php $ogImagem = $produto->getFirstMediaUrl('images', 'thumb'); @endphp
    <meta property="og:title"       content="{{ $produto->name }} — {{ $lojaAtual->name }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($produto->description), 160) }}">
    @if($ogImagem)
    <meta property="og:image" content="{{ $ogImagem }}">
    @endif
    <meta property="og:type" content="product">
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
        <a href="{{ route('loja.home', $lojaAtual->slug) }}" class="hover:text-emerald-600 transition-colors">Início</a>
        <span>/</span>
        <a href="{{ route('loja.produtos.index', $lojaAtual->slug) }}" class="hover:text-emerald-600 transition-colors">Produtos</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">{{ $produto->name }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">

        {{-- Galeria de imagens --}}
        <div x-data="{ imagemAtiva: 0 }">
            @php $imagens = $produto->getMedia('images'); @endphp

            <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden mb-4 shadow-sm">
                @if($imagens->isNotEmpty())
                    @foreach($imagens as $i => $midia)
                    <img src="{{ $midia->getUrl() }}"
                         alt="{{ $produto->name }}"
                         x-show="imagemAtiva === {{ $i }}"
                         class="w-full h-full object-cover">
                    @endforeach
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-200">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
            </div>

            @if($imagens->count() > 1)
            <div class="flex gap-2 overflow-x-auto">
                @foreach($imagens as $i => $midia)
                <button @click="imagemAtiva = {{ $i }}"
                        :class="imagemAtiva === {{ $i }} ? 'ring-2 ring-emerald-500' : 'opacity-60 hover:opacity-100'"
                        class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden transition-all">
                    <img src="{{ $midia->getUrl('thumb') }}" alt="" class="w-full h-full object-cover">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Informações do produto --}}
        <div class="flex flex-col">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $produto->name }}</h1>

            {{-- Preço --}}
            <div class="mb-6">
                @if($produto->compare_price && $produto->compare_price > $produto->price)
                    <p class="text-sm text-gray-400 line-through mb-1">
                        R$ {{ number_format($produto->compare_price / 100, 2, ',', '.') }}
                    </p>
                    @php
                        $desconto = round((($produto->compare_price - $produto->price) / $produto->compare_price) * 100);
                    @endphp
                    <span class="inline-block bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full mb-2">
                        -{{ $desconto }}%
                    </span>
                @endif
                <p class="text-4xl font-bold text-emerald-600">
                    R$ {{ number_format($produto->price / 100, 2, ',', '.') }}
                </p>
            </div>

            {{-- SKU e estoque --}}
            @if($produto->sku)
            <p class="text-xs text-gray-400 mb-2">SKU: {{ $produto->sku }}</p>
            @endif

            @if($produto->stock > 0)
                <p class="text-sm text-emerald-600 font-medium mb-6">
                    ✓ {{ $produto->stock }} unidade(s) em estoque
                </p>
            @else
                <p class="text-sm text-red-500 font-medium mb-6">✗ Fora de estoque</p>
            @endif

            {{-- Variantes --}}
            @if($produto->variants->isNotEmpty())
            <div class="mb-6">
                <p class="text-sm font-medium text-gray-700 mb-2">Variantes:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($produto->variants as $variante)
                    <span class="px-3 py-1 border border-gray-300 rounded-full text-sm hover:border-emerald-500 cursor-pointer transition-colors">
                        {{ $variante->name }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Botão adicionar ao carrinho --}}
            @if($produto->stock > 0)
                @livewire('storefront.adicionar-ao-carrinho', [
                    'productId' => $produto->id,
                    'maxStock'  => $produto->stock,
                ])
            @else
                <button disabled
                        class="w-full bg-gray-200 text-gray-400 font-semibold py-3 px-6 rounded-xl cursor-not-allowed">
                    Produto indisponível
                </button>
            @endif

            {{-- Descrição --}}
            @if($produto->description)
            <div class="mt-8 pt-8 border-t border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Descrição</h2>
                <div class="prose prose-sm text-gray-600 max-w-none">
                    {!! nl2br(e($produto->description)) !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
