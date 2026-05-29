@extends('storefront.layouts.loja')

@section('title', 'Pedido #' . $pedido->id)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Pedido #{{ $pedido->id }}</h1>
        <a href="{{ route('loja.conta.pedidos', $lojaAtual->slug) }}"
           class="text-sm text-emerald-600 hover:text-emerald-700 transition-colors">
            ← Meus pedidos
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Coluna principal --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Itens --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Itens do pedido</h2>
                <div class="divide-y divide-gray-100">
                    @foreach($pedido->items as $item)
                    <div class="flex justify-between items-center py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $item->product_name }}</p>
                            @if($item->variant_name)
                            <p class="text-xs text-gray-500">{{ $item->variant_name }}</p>
                            @endif
                            <p class="text-xs text-gray-400">{{ $item->quantity }}× R$ {{ number_format($item->unit_price / 100, 2, ',', '.') }}</p>
                        </div>
                        <p class="text-sm font-semibold text-gray-900">
                            R$ {{ number_format($item->unit_price * $item->quantity / 100, 2, ',', '.') }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Rastreio --}}
            @if($pedido->tracking_code)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Rastreamento</h2>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2h8l2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Código de rastreio</p>
                        <p class="text-base font-bold text-gray-900">{{ $pedido->tracking_code }}</p>
                        @if($pedido->tracking_status)
                        <p class="text-sm text-purple-600">{{ $pedido->tracking_status }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Histórico de status --}}
            @if($pedido->statusHistory->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Histórico</h2>
                <div class="relative pl-4 border-l-2 border-gray-200 space-y-4">
                    @foreach($pedido->statusHistory as $historico)
                    <div class="relative">
                        <div class="absolute -left-[21px] w-3 h-3 bg-emerald-400 rounded-full border-2 border-white"></div>
                        <p class="text-sm font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $historico->status) }}</p>
                        @if($historico->comment)
                        <p class="text-xs text-gray-500">{{ $historico->comment }}</p>
                        @endif
                        <p class="text-xs text-gray-400">{{ $historico->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar de resumo --}}
        <div class="space-y-6">

            {{-- Totais --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Resumo</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>R$ {{ number_format($pedido->subtotal / 100, 2, ',', '.') }}</span>
                    </div>
                    @if($pedido->discount > 0)
                    <div class="flex justify-between text-emerald-600">
                        <span>Desconto</span>
                        <span>-R$ {{ number_format($pedido->discount / 100, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($pedido->shipping_cost > 0)
                    <div class="flex justify-between text-gray-600">
                        <span>Frete</span>
                        <span>R$ {{ number_format($pedido->shipping_cost / 100, 2, ',', '.') }}</span>
                    </div>
                    @elseif($pedido->shipping_cost === 0 && $pedido->recipient_address)
                    <div class="flex justify-between text-emerald-600">
                        <span>Frete</span>
                        <span>Grátis</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-bold text-gray-900 pt-2 border-t border-gray-100 text-base">
                        <span>Total</span>
                        <span>R$ {{ number_format($pedido->total / 100, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Endereço de entrega --}}
            @if($pedido->recipient_address)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Endereço de entrega</h2>
                <p class="text-sm font-medium text-gray-700">{{ $pedido->recipient_name }}</p>
                <p class="text-sm text-gray-600">
                    {{ $pedido->recipient_address }}, {{ $pedido->recipient_number }}
                    @if($pedido->recipient_complement) — {{ $pedido->recipient_complement }} @endif
                </p>
                <p class="text-sm text-gray-600">{{ $pedido->recipient_city }} / {{ $pedido->recipient_state }}</p>
                <p class="text-sm text-gray-500">CEP: {{ $pedido->recipient_zipcode }}</p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
