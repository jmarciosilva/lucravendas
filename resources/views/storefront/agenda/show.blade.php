@extends('storefront.layouts.loja')

@section('title', $item->title)
@section('description', $item->short_description ?? $item->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Link de voltar --}}
    <div class="mb-6">
        <a href="{{ route('loja.agenda.index', $lojaAtual->slug) }}"
           class="inline-flex items-center text-sm text-gray-500 hover:text-emerald-600 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para a Agenda
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Imagem de destaque --}}
        @if($item->featured_image_url)
            <div class="aspect-video bg-gray-100 overflow-hidden">
                <img src="{{ $item->featured_image_url }}"
                     alt="{{ $item->title }}"
                     class="w-full h-full object-cover">
            </div>
        @else
            <div class="aspect-video bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center">
                <svg class="w-24 h-24 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif

        <div class="p-6 sm:p-8">

            {{-- Badge do tipo --}}
            @php
                $tipoColors = [
                    'evento'   => 'bg-blue-100 text-blue-700',
                    'curso'    => 'bg-purple-100 text-purple-700',
                    'workshop' => 'bg-amber-100 text-amber-700',
                ];
                $tipoLabels = ['evento' => 'Evento', 'curso' => 'Curso', 'workshop' => 'Workshop'];
                $tipoColor = $tipoColors[$item->type] ?? 'bg-gray-100 text-gray-700';
                $tipoLabel = $tipoLabels[$item->type] ?? $item->type;
            @endphp
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide {{ $tipoColor }} mb-4">
                {{ $tipoLabel }}
            </span>

            {{-- Título --}}
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">{{ $item->title }}</h1>

            {{-- Informações rápidas --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-xl">

                {{-- Data e hora --}}
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase">Início</p>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $item->starts_at->format('d/m/Y') }}
                            às {{ $item->starts_at->format('H:i') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Término: {{ $item->ends_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>

                {{-- Local --}}
                @if($item->location)
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase">Local</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $item->location }}</p>
                    </div>
                </div>
                @endif

                {{-- Vagas --}}
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase">Vagas</p>
                        @if($item->hasSlots())
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $item->slotsAvailable() }} disponível(is)
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $item->slots_used }} de {{ $item->slots }} preenchidas
                            </p>
                        @else
                            <p class="text-sm font-semibold text-gray-800">Vagas ilimitadas</p>
                        @endif
                    </div>
                </div>

                {{-- Preço --}}
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase">Valor</p>
                        @if($item->isFree())
                            <p class="text-sm font-bold text-emerald-600">Gratuito</p>
                        @else
                            <p class="text-sm font-bold text-gray-800">
                                R$ {{ number_format($item->price_centavos / 100, 2, ',', '.') }}
                            </p>
                        @endif
                    </div>
                </div>

            </div>

            {{-- Descrição completa --}}
            @if($item->description)
                <div class="prose prose-sm max-w-none text-gray-700 mb-8">
                    {!! nl2br(e($item->description)) !!}
                </div>
            @endif

            {{-- Área de inscrição --}}
            <div class="border-t border-gray-100 pt-6">
                @auth
                    @livewire('storefront.inscricao-agenda', [
                        'agendaItemId'  => $item->id,
                        'isFree'        => $item->isFree(),
                        'priceCentavos' => $item->price_centavos,
                        'tenantSlug'    => $lojaAtual->slug,
                    ])
                @else
                    <div class="text-center py-4 bg-gray-50 rounded-xl">
                        <p class="text-gray-600 mb-4">Faça login para se inscrever neste evento.</p>
                        <a href="{{ route('loja.login', $lojaAtual->slug) }}"
                           class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white rounded-full text-sm font-semibold hover:bg-emerald-700 transition-colors">
                            Entrar para se inscrever
                        </a>
                    </div>
                @endauth
            </div>

        </div>
    </div>

</div>
@endsection
