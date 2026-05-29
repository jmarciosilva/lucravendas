@extends('storefront.layouts.loja')

@section('title', $lojaAtual->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Banner de boas-vindas --}}
    <section class="bg-gradient-to-r from-emerald-600 to-emerald-500 rounded-2xl p-10 mb-12 text-white text-center shadow-md">
        <h1 class="text-3xl md:text-4xl font-bold mb-3">Bem-vindo à {{ $lojaAtual->name }}</h1>
        <p class="text-emerald-100 text-lg mb-6">Encontre os melhores produtos com os melhores preços.</p>
        <a href="{{ route('loja.produtos.index', $lojaAtual->slug) }}"
           class="inline-block bg-white text-emerald-600 font-semibold px-8 py-3 rounded-full hover:bg-emerald-50 transition-colors shadow-sm">
            Ver todos os produtos
        </a>
    </section>

    {{-- Categorias --}}
    @if(!empty($categorias))
    <section class="mb-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Categorias</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($categorias as $categoria)
            <a href="{{ route('loja.produtos.index', $lojaAtual->slug) }}?categoria={{ $categoria->id }}"
               class="flex flex-col items-center p-4 bg-white rounded-xl shadow-sm hover:shadow-md hover:border-emerald-300 border border-transparent transition-all text-center group">
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-emerald-200 transition-colors">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-700 group-hover:text-emerald-600 transition-colors">
                    {{ $categoria->name }}
                </span>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Produtos em destaque --}}
    @if($destaques->count() > 0)
    <section>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Produtos em destaque</h2>
            <a href="{{ route('loja.produtos.index', $lojaAtual->slug) }}"
               class="text-sm text-emerald-600 hover:text-emerald-700 font-medium transition-colors">
                Ver todos →
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
            @foreach($destaques as $produto)
            @include('storefront.produto.card', ['produto' => $produto])
            @endforeach
        </div>
    </section>
    @else
    <div class="text-center py-16 text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <p class="text-lg">Nenhum produto disponível ainda.</p>
    </div>
    @endif

</div>
@endsection
