<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="font-semibold text-gray-800 mb-5">Filtrar</h3>

    {{-- Busca --}}
    <div class="mb-5">
        <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide mb-2">Busca</label>
        <input type="text"
               wire:model="busca"
               placeholder="Nome do produto..."
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
    </div>

    {{-- Categoria --}}
    @if(!empty($categorias))
    <div class="mb-5">
        <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide mb-2">Categoria</label>
        <select wire:model="categoriaId"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="">Todas as categorias</option>
            @foreach($categorias as $cat)
            <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Faixa de preço --}}
    <div class="mb-5">
        <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide mb-2">Preço (R$)</label>
        <div class="flex gap-2">
            <input type="number"
                   wire:model="precoMin"
                   placeholder="Mín"
                   min="0"
                   class="w-1/2 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <input type="number"
                   wire:model="precoMax"
                   placeholder="Máx"
                   min="0"
                   class="w-1/2 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>
    </div>

    {{-- Ordenação --}}
    <div class="mb-6">
        <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide mb-2">Ordenar por</label>
        <select wire:model="ordenacao"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="recente">Mais recentes</option>
            <option value="preco_asc">Menor preço</option>
            <option value="preco_desc">Maior preço</option>
            <option value="nome">Nome A-Z</option>
        </select>
    </div>

    {{-- Ações --}}
    <div class="space-y-2">
        <button wire:click="aplicarFiltros"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
            Aplicar filtros
        </button>
        <button wire:click="limparFiltros"
                class="w-full border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-medium py-2 rounded-lg transition-colors">
            Limpar
        </button>
    </div>
</div>
