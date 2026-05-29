@extends('storefront.layouts.loja')

@section('title', ($categoriaAtual ? $categoriaAtual->name . ' — ' : '') . 'Produtos')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('loja.home', $lojaAtual->slug) }}" class="hover:text-emerald-600 transition-colors">Início</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">
            {{ $categoriaAtual ? $categoriaAtual->name : 'Produtos' }}
        </span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar de filtros --}}
        <aside class="lg:w-64 flex-shrink-0">
            @livewire('storefront.catalogo-filtros', [
                'tenantId'   => $lojaAtual->id,
                'tenantSlug' => $lojaAtual->slug,
            ])
        </aside>

        {{-- Grade de produtos --}}
        <div class="flex-1">

            {{-- Barra de resultado --}}
            <div class="flex items-center justify-between mb-6">
                <p class="text-sm text-gray-500">
                    {{ $produtos->total() }} produto(s) encontrado(s)
                </p>
            </div>

            @if($produtos->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
                    @foreach($produtos as $produto)
                        @include('storefront.produto.card', ['produto' => $produto])
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $produtos->links() }}
                </div>
            @else
                <div class="text-center py-20 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg">Nenhum produto encontrado.</p>
                    <a href="{{ route('loja.produtos.index', $lojaAtual->slug) }}"
                       class="mt-4 inline-block text-emerald-600 hover:underline text-sm">
                        Limpar filtros
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
