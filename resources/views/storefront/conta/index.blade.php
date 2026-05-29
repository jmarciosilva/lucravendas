@extends('storefront.layouts.loja')

@section('title', 'Minha conta')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <h1 class="text-3xl font-bold text-gray-900 mb-8">Minha conta</h1>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <div class="flex items-center gap-6 mb-8">
            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center">
                <span class="text-2xl font-bold text-emerald-600">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ auth()->user()->name }}</h2>
                <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                @if(auth()->user()->phone)
                <p class="text-sm text-gray-500">{{ auth()->user()->phone }}</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('loja.conta.pedidos', $lojaAtual->slug) }}"
               class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl hover:border-emerald-300 hover:bg-emerald-50 transition-all group">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-800 group-hover:text-emerald-700">Meus pedidos</p>
                    <p class="text-xs text-gray-500">Acompanhe suas compras</p>
                </div>
            </a>

            <a href="{{ route('loja.produtos.index', $lojaAtual->slug) }}"
               class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl hover:border-emerald-300 hover:bg-emerald-50 transition-all group">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-800 group-hover:text-emerald-700">Continuar comprando</p>
                    <p class="text-xs text-gray-500">Ver produtos disponíveis</p>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection
