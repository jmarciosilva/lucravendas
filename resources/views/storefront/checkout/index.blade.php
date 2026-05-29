@extends('storefront.layouts.loja')

@section('title', 'Finalizar pedido')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <h1 class="text-3xl font-bold text-gray-900 mb-8">Finalizar pedido</h1>

    @guest
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center">
        <p class="text-amber-800 font-medium mb-4">Você precisa estar autenticado para finalizar o pedido.</p>
        <a href="{{ route('loja.login', $lojaAtual->slug) }}"
           class="inline-block bg-emerald-600 text-white font-semibold px-6 py-2.5 rounded-xl hover:bg-emerald-700 transition-colors">
            Entrar na conta
        </a>
    </div>
    @else
    @livewire('storefront.checkout-form')
    @endguest

</div>
@endsection
