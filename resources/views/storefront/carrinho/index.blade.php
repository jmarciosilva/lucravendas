@extends('storefront.layouts.loja')

@section('title', 'Carrinho de compras')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <h1 class="text-3xl font-bold text-gray-900 mb-8">Carrinho de compras</h1>

    @livewire('storefront.carrinho-page')

</div>
@endsection
