<div>
    @if($erro)
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm mb-6">
        {{ $erro }}
    </div>
    @endif

    {{-- Indicador de passos --}}
    <div class="flex items-center gap-2 mb-10">
        @foreach([1 => 'Endereço', 2 => 'Frete', 3 => 'Pagamento'] as $num => $label)
        <div class="flex items-center {{ $num < 3 ? 'flex-1' : '' }}">
            <div class="flex items-center gap-2">
                <div @class([
                    'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors',
                    'bg-emerald-600 text-white' => $passo >= $num,
                    'bg-gray-200 text-gray-500' => $passo < $num,
                ])>
                    @if($passo > $num)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    @else
                    {{ $num }}
                    @endif
                </div>
                <span @class([
                    'text-sm font-medium hidden sm:block transition-colors',
                    'text-emerald-700' => $passo >= $num,
                    'text-gray-400' => $passo < $num,
                ])>{{ $label }}</span>
            </div>
            @if($num < 3)
            <div @class([
                'flex-1 h-0.5 mx-3 transition-colors',
                'bg-emerald-400' => $passo > $num,
                'bg-gray-200' => $passo <= $num,
            ])></div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Passo 1: Endereço --}}
    @if($passo === 1)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Endereço de entrega</h2>

        <div class="space-y-4"
             x-data="{
                 buscarCep() {
                     let cep = $wire.cep.replace(/\D/g, '');
                     if (cep.length === 8) {
                         fetch('https://viacep.com.br/ws/' + cep + '/json/')
                             .then(r => r.json())
                             .then(d => {
                                 if (!d.erro) {
                                     $wire.set('endereco', d.logradouro || '');
                                     $wire.set('cidade', d.localidade || '');
                                     $wire.set('estado', d.uf || '');
                                 }
                             })
                             .catch(() => {});
                     }
                 }
             }">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome do destinatário *</label>
                <input type="text"
                       wire:model="nomeDestinatario"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('nomeDestinatario') border-red-400 @enderror">
                @error('nomeDestinatario') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP *</label>
                    <input type="text"
                           wire:model="cep"
                           @blur="buscarCep()"
                           placeholder="00000-000"
                           maxlength="9"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('cep') border-red-400 @enderror">
                    @error('cep') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereço *</label>
                    <input type="text"
                           wire:model="endereco"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('endereco') border-red-400 @enderror">
                    @error('endereco') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número *</label>
                    <input type="text"
                           wire:model="numero"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('numero') border-red-400 @enderror">
                    @error('numero') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                    <input type="text"
                           wire:model="complemento"
                           placeholder="Apto, bloco..."
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade *</label>
                    <input type="text"
                           wire:model="cidade"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('cidade') border-red-400 @enderror">
                    @error('cidade') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado (UF) *</label>
                    <input type="text"
                           wire:model="estado"
                           maxlength="2"
                           placeholder="SP"
                           style="text-transform: uppercase"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 uppercase @error('estado') border-red-400 @enderror">
                    @error('estado') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-8">
            <button wire:click="proximoPasso"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-75"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-8 py-3 rounded-xl transition-colors">
                <span wire:loading.remove>Continuar →</span>
                <span wire:loading>Calculando frete...</span>
            </button>
        </div>
    </div>
    @endif

    {{-- Passo 2: Frete --}}
    @if($passo === 2)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Opções de entrega</h2>

        @if(empty($opcoesFreteRaw))
        <div class="text-center py-10 text-gray-400">
            <p>Nenhuma opção de frete disponível para o CEP informado.</p>
            <p class="text-xs mt-1">Verifique o CEP ou entre em contato com a loja.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($opcoesFreteRaw as $opcao)
            <label class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-all
                          {{ $freteEscolhido === $opcao['id'] ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300' }}">
                <input type="radio"
                       wire:model="freteEscolhido"
                       value="{{ $opcao['id'] }}"
                       class="accent-emerald-600">
                <div class="flex-1">
                    <p class="font-medium text-gray-800">{{ $opcao['nome'] }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $opcao['transportadora'] }} ·
                        {{ $opcao['minDias'] === $opcao['maxDias']
                            ? $opcao['minDias'] . ' dias úteis'
                            : $opcao['minDias'] . ' a ' . $opcao['maxDias'] . ' dias úteis' }}
                    </p>
                </div>
                <div class="text-right flex-shrink-0">
                    @if($opcao['gratis'])
                    <span class="text-emerald-600 font-bold text-sm">Grátis</span>
                    @else
                    <span class="font-bold text-gray-900">R$ {{ number_format($opcao['preco'] / 100, 2, ',', '.') }}</span>
                    @endif
                </div>
            </label>
            @endforeach
        </div>
        @endif

        @if($erro)
        <p class="text-red-600 text-sm mt-3">{{ $erro }}</p>
        @endif

        <div class="flex justify-between mt-8">
            <button wire:click="voltarPasso"
                    class="border border-gray-300 text-gray-700 font-medium px-6 py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                ← Voltar
            </button>
            <button wire:click="proximoPasso"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-8 py-3 rounded-xl transition-colors">
                Continuar →
            </button>
        </div>
    </div>
    @endif

    {{-- Passo 3: Pagamento --}}
    @if($passo === 3)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Forma de pagamento</h2>

        <div class="space-y-3 mb-6">
            @foreach(['pix' => ['label' => 'PIX', 'desc' => 'Aprovação imediata', 'cor' => 'text-teal-600'],
                       'card' => ['label' => 'Cartão de crédito', 'desc' => 'Via Mercado Pago', 'cor' => 'text-blue-600'],
                       'boleto' => ['label' => 'Boleto bancário', 'desc' => 'Vencimento em 3 dias úteis', 'cor' => 'text-orange-600']] as $valor => $info)
            <label class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-all
                          {{ $metodoPagamento === $valor ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300' }}">
                <input type="radio"
                       wire:model="metodoPagamento"
                       value="{{ $valor }}"
                       class="accent-emerald-600">
                <div class="flex-1">
                    <p class="font-medium text-gray-800">{{ $info['label'] }}</p>
                    <p class="text-xs {{ $info['cor'] }}">{{ $info['desc'] }}</p>
                </div>
            </label>
            @endforeach
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações (opcional)</label>
            <textarea wire:model="notas"
                      rows="2"
                      placeholder="Instruções especiais para a entrega..."
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
        </div>

        <div class="bg-gray-50 rounded-xl p-4 mb-6 text-sm">
            <p class="text-gray-600 font-medium mb-2">Resumo</p>
            <div class="flex justify-between text-gray-700">
                <span>Subtotal</span>
                <span>R$ {{ number_format($subtotalCentavos / 100, 2, ',', '.') }}</span>
            </div>
            @if($freteEscolhido)
            @php
                $opcaoSelecionada = collect($opcoesFreteRaw)->firstWhere('id', $freteEscolhido);
            @endphp
            @if($opcaoSelecionada)
            <div class="flex justify-between text-gray-700 mt-1">
                <span>Frete ({{ $opcaoSelecionada['nome'] }})</span>
                <span>{{ $opcaoSelecionada['gratis'] ? 'Grátis' : 'R$ ' . number_format($opcaoSelecionada['preco'] / 100, 2, ',', '.') }}</span>
            </div>
            @endif
            @endif
        </div>

        <div class="flex justify-between">
            <button wire:click="voltarPasso"
                    class="border border-gray-300 text-gray-700 font-medium px-6 py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                ← Voltar
            </button>
            <button wire:click="finalizar"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-75 cursor-not-allowed"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-8 py-3 rounded-xl transition-colors flex items-center gap-2">
                <span wire:loading.remove>Confirmar pedido</span>
                <span wire:loading>Processando...</span>
            </button>
        </div>
    </div>
    @endif
</div>
