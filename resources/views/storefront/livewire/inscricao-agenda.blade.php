{{-- Componente Livewire: botão e estado de inscrição em item de agenda --}}
<div>
    {{-- Estado: já inscrito com sucesso --}}
    @if($inscrito)
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
            <svg class="w-6 h-6 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-semibold text-emerald-800">Inscrição confirmada!</p>
                <p class="text-sm text-emerald-600">Você está inscrito neste evento.</p>
            </div>
        </div>

    {{-- Estado: erro na inscrição --}}
    @elseif($erro)
        <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-xl mb-4">
            <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-red-700">{{ $erro }}</p>
        </div>

    @else

        {{-- Informação de preço para eventos pagos --}}
        @if(!$isFree)
            <div class="flex items-center gap-2 mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-amber-700">
                    Valor: <strong>R$ {{ number_format($priceCentavos / 100, 2, ',', '.') }}</strong>
                    — pagamento via PIX ou boleto após a inscrição.
                </p>
            </div>
        @endif

        {{-- Botão de inscrição --}}
        <button wire:click="inscrever"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-75 cursor-not-allowed"
                class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-full text-sm font-semibold hover:bg-emerald-700 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">

            {{-- Estado de carregamento --}}
            <span wire:loading wire:target="inscrever">
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </span>

            <span wire:loading.remove wire:target="inscrever">
                @if($isFree)
                    Inscrever-se gratuitamente
                @else
                    Inscrever-se (pagamento após)
                @endif
            </span>
            <span wire:loading wire:target="inscrever">Processando...</span>
        </button>

    @endif
</div>
