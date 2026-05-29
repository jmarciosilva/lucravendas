@extends('storefront.layouts.loja')

@section('title', 'Agenda de Eventos')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Cabeçalho da seção --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Agenda de Eventos</h1>
        <p class="mt-2 text-gray-500">Confira os próximos eventos, cursos e workshops disponíveis.</p>
    </div>

    {{-- Grid de itens ou mensagem de vazio --}}
    @if($itens->isEmpty())
        <div class="text-center py-16">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-500 text-lg">Nenhum evento disponível no momento.</p>
            <p class="text-gray-400 text-sm mt-1">Volte em breve para conferir as novidades!</p>
        </div>
    @else
        {{-- Grid de 3 colunas com cards de agenda --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($itens as $item)
                @livewire('storefront.agenda-card', [
                    'agendaItemId' => $item->id,
                    'tenantSlug'   => $lojaAtual->slug,
                ], key($item->id))
            @endforeach
        </div>
    @endif

</div>
@endsection
