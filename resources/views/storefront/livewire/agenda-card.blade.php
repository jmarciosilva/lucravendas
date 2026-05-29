{{-- Componente Livewire: card resumido de item de agenda --}}
@php
    $tipoColors = [
        'evento'   => 'bg-blue-100 text-blue-700',
        'curso'    => 'bg-purple-100 text-purple-700',
        'workshop' => 'bg-amber-100 text-amber-700',
    ];
    $tipoLabels = ['evento' => 'Evento', 'curso' => 'Curso', 'workshop' => 'Workshop'];
    $tipoColor = $tipoColors[$tipo] ?? 'bg-gray-100 text-gray-700';
    $tipoLabel = $tipoLabels[$tipo] ?? $tipo;
@endphp

<a href="{{ route('loja.agenda.show', [$tenantSlug, $slug]) }}"
   class="group bg-white rounded-xl shadow-sm hover:shadow-md border border-gray-100 overflow-hidden transition-all flex flex-col">

    {{-- Imagem de destaque ou placeholder --}}
    <div class="aspect-video bg-gray-100 overflow-hidden">
        @if($imagemUrl)
            <img src="{{ $imagemUrl }}"
                 alt="{{ $titulo }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-emerald-50 to-emerald-100">
                <svg class="w-14 h-14 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif
    </div>

    {{-- Conteúdo do card --}}
    <div class="p-4 flex flex-col flex-1">

        {{-- Badge do tipo --}}
        <span class="inline-block self-start px-2 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide {{ $tipoColor }} mb-2">
            {{ $tipoLabel }}
        </span>

        {{-- Título --}}
        <h3 class="text-sm font-semibold text-gray-800 group-hover:text-emerald-600 transition-colors line-clamp-2 mb-3">
            {{ $titulo }}
        </h3>

        <div class="mt-auto space-y-2">

            {{-- Data --}}
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>{{ $startsAt }}</span>
            </div>

            {{-- Vagas --}}
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                @if($slotsDisponiveis === null)
                    <span>Vagas ilimitadas</span>
                @elseif($slotsDisponiveis > 0)
                    <span>{{ $slotsDisponiveis }} vaga(s) disponível(is)</span>
                @else
                    <span class="text-red-500 font-medium">Vagas esgotadas</span>
                @endif
            </div>

            {{-- Preço --}}
            @if($isFree)
                <span class="inline-block px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">
                    Gratuito
                </span>
            @else
                <p class="text-sm font-bold text-gray-800">
                    R$ {{ number_format($priceCentavos / 100, 2, ',', '.') }}
                </p>
            @endif

        </div>
    </div>

</a>
