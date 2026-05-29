@extends('storefront.layouts.loja')

@section('title', 'Pedido #' . $pedido->id . ' confirmado')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- Banner de sucesso --}}
    <div class="text-center mb-10">
        <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Pedido realizado!</h1>
        <p class="text-gray-500">Pedido <span class="font-semibold text-gray-700">#{{ $pedido->id }}</span></p>
    </div>

    {{-- Instruções de pagamento --}}
    @if($transacao)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Pagamento</h2>

        @if($transacao->method === 'pix' && $transacao->qr_code)
        <div class="text-center">
            <p class="text-sm text-gray-600 mb-4">Escaneie o QR Code para pagar via PIX:</p>
            @if($transacao->qr_code_base64)
            <img src="data:image/png;base64,{{ $transacao->qr_code_base64 }}"
                 alt="QR Code PIX"
                 class="w-48 h-48 mx-auto mb-4 border border-gray-200 rounded-xl p-2">
            @endif
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs text-gray-600 break-all mb-2">
                {{ $transacao->qr_code }}
            </div>
            <button onclick="navigator.clipboard.writeText('{{ $transacao->qr_code }}')"
                    class="text-xs text-emerald-600 hover:underline">
                Copiar código PIX
            </button>
        </div>

        @elseif($transacao->method === 'boleto' && $transacao->ticket_url)
        <div class="text-center">
            <p class="text-sm text-gray-600 mb-4">Clique abaixo para visualizar e pagar o boleto:</p>
            <a href="{{ $transacao->ticket_url }}"
               target="_blank"
               class="inline-block bg-blue-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-blue-700 transition-colors">
                Abrir boleto bancário
            </a>
        </div>

        @elseif($transacao->method === 'card')
        <div class="text-center">
            <p class="text-sm text-emerald-700 font-medium">
                ✓ Pagamento com cartão processado.
                Status: <span class="capitalize">{{ $transacao->status }}</span>
            </p>
        </div>
        @endif
    </div>
    @endif

    {{-- Resumo do pedido --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Resumo do pedido</h2>

        <div class="space-y-3 mb-6">
            @foreach($pedido->items as $item)
            <div class="flex justify-between text-sm">
                <span class="text-gray-700">{{ $item->product_name }} × {{ $item->quantity }}</span>
                <span class="font-medium text-gray-900">
                    R$ {{ number_format($item->unit_price * $item->quantity / 100, 2, ',', '.') }}
                </span>
            </div>
            @endforeach
        </div>

        <div class="border-t border-gray-100 pt-4 space-y-2">
            <div class="flex justify-between text-sm text-gray-600">
                <span>Subtotal</span>
                <span>R$ {{ number_format($pedido->subtotal / 100, 2, ',', '.') }}</span>
            </div>
            @if($pedido->discount > 0)
            <div class="flex justify-between text-sm text-emerald-600">
                <span>Desconto</span>
                <span>-R$ {{ number_format($pedido->discount / 100, 2, ',', '.') }}</span>
            </div>
            @endif
            @if($pedido->shipping_cost > 0)
            <div class="flex justify-between text-sm text-gray-600">
                <span>Frete</span>
                <span>R$ {{ number_format($pedido->shipping_cost / 100, 2, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t border-gray-100">
                <span>Total</span>
                <span>R$ {{ number_format($pedido->total / 100, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Endereço de entrega --}}
    @if($pedido->recipient_address)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Entrega</h2>
        <p class="text-sm text-gray-700">{{ $pedido->recipient_name }}</p>
        <p class="text-sm text-gray-600">
            {{ $pedido->recipient_address }}, {{ $pedido->recipient_number }}
            @if($pedido->recipient_complement) — {{ $pedido->recipient_complement }} @endif
        </p>
        <p class="text-sm text-gray-600">{{ $pedido->recipient_city }} / {{ $pedido->recipient_state }}</p>
        <p class="text-sm text-gray-600">CEP: {{ $pedido->recipient_zipcode }}</p>
    </div>
    @endif

    {{-- Ações --}}
    <div class="flex flex-col sm:flex-row gap-4">
        <a href="{{ route('loja.conta.pedidos', $lojaAtual->slug) }}"
           class="flex-1 text-center bg-emerald-600 text-white font-semibold py-3 rounded-xl hover:bg-emerald-700 transition-colors">
            Ver meus pedidos
        </a>
        <a href="{{ route('loja.home', $lojaAtual->slug) }}"
           class="flex-1 text-center border border-gray-300 text-gray-700 font-semibold py-3 rounded-xl hover:bg-gray-50 transition-colors">
            Continuar comprando
        </a>
    </div>

</div>
@endsection
