@extends('storefront.layouts.loja')

@section('title', 'Meus pedidos')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Meus pedidos</h1>
        <a href="{{ route('loja.conta.index', $lojaAtual->slug) }}"
           class="text-sm text-emerald-600 hover:text-emerald-700 transition-colors">
            ← Minha conta
        </a>
    </div>

    @if($pedidos->isNotEmpty())
    <div class="space-y-4">
        @foreach($pedidos as $pedido)
        <a href="{{ route('loja.conta.pedido', [$lojaAtual->slug, $pedido->id]) }}"
           class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:border-emerald-200 hover:shadow-md transition-all">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="font-semibold text-gray-900">Pedido #{{ $pedido->id }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $pedido->created_at->format('d/m/Y \à\s H:i') }}</p>
                </div>

                <div class="flex items-center gap-6">
                    <div class="text-right sm:text-center">
                        <p class="text-xs text-gray-400">Total</p>
                        <p class="font-bold text-gray-900">R$ {{ number_format($pedido->total / 100, 2, ',', '.') }}</p>
                    </div>

                    <div class="text-right">
                        @php
                            $statusCores = [
                                'pending'    => 'bg-yellow-100 text-yellow-700',
                                'confirmed'  => 'bg-blue-100 text-blue-700',
                                'processing' => 'bg-blue-100 text-blue-700',
                                'shipped'    => 'bg-purple-100 text-purple-700',
                                'delivered'  => 'bg-emerald-100 text-emerald-700',
                                'cancelled'  => 'bg-red-100 text-red-700',
                            ];
                            $statusLabels = [
                                'pending'    => 'Aguardando',
                                'confirmed'  => 'Confirmado',
                                'processing' => 'Em preparo',
                                'shipped'    => 'Enviado',
                                'delivered'  => 'Entregue',
                                'cancelled'  => 'Cancelado',
                            ];
                            $cor   = $statusCores[$pedido->status] ?? 'bg-gray-100 text-gray-600';
                            $label = $statusLabels[$pedido->status] ?? ucfirst($pedido->status);
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $cor }}">
                            {{ $label }}
                        </span>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $pedidos->links() }}
    </div>

    @else
    <div class="text-center py-20 text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-lg">Você ainda não fez nenhum pedido.</p>
        <a href="{{ route('loja.produtos.index', $lojaAtual->slug) }}"
           class="mt-4 inline-block text-emerald-600 hover:underline text-sm">
            Ver produtos
        </a>
    </div>
    @endif

</div>
@endsection
