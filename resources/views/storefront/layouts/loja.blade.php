<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $lojaAtual->name) — {{ $lojaAtual->name }}</title>
    <meta name="description" content="@yield('description', 'Loja online ' . $lojaAtual->name)">
    @yield('og_meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

    {{-- Header --}}
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo / Nome da loja --}}
                <a href="{{ route('loja.home', $lojaAtual->slug) }}"
                   class="text-xl font-bold text-emerald-600 hover:text-emerald-700 transition-colors">
                    {{ $lojaAtual->name }}
                </a>

                {{-- Busca rápida --}}
                <form action="{{ route('loja.produtos.index', $lojaAtual->slug) }}"
                      method="GET"
                      class="hidden md:flex items-center flex-1 max-w-md mx-8">
                    <div class="relative w-full">
                        <input type="text"
                               name="q"
                               value="{{ request('q') }}"
                               placeholder="Buscar produtos..."
                               class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <button type="submit"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-emerald-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- Ações --}}
                <div class="flex items-center gap-4">
                    @guest
                        <a href="{{ route('loja.login', $lojaAtual->slug) }}"
                           class="text-sm text-gray-600 hover:text-emerald-600 transition-colors">
                            Entrar
                        </a>
                    @else
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center gap-1 text-sm text-gray-700 hover:text-emerald-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ Str::words(auth()->user()->name, 1, '') }}
                            </button>
                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                                <a href="{{ route('loja.conta.index', $lojaAtual->slug) }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    Minha conta
                                </a>
                                <a href="{{ route('loja.conta.pedidos', $lojaAtual->slug) }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    Meus pedidos
                                </a>
                                <form method="POST" action="{{ route('loja.logout', $lojaAtual->slug) }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        Sair
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest

                    {{-- Mini carrinho --}}
                    @livewire('storefront.carrinho-widget')
                </div>
            </div>
        </div>
    </header>

    {{-- Navegação de categorias --}}
    @hasSection('nav_categorias')
    @yield('nav_categorias')
    @else
    @endif

    {{-- Conteúdo principal --}}
    <main class="flex-1">
        @if(session('sucesso'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm">
                    {{ session('sucesso') }}
                </div>
            </div>
        @endif

        @if(session('erro'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                    {{ session('erro') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-500">
                    &copy; {{ now()->year }} {{ $lojaAtual->name }}. Todos os direitos reservados.
                </p>
                <div class="flex gap-6 text-sm text-gray-400">
                    <a href="{{ route('loja.produtos.index', $lojaAtual->slug) }}"
                       class="hover:text-emerald-600 transition-colors">
                        Produtos
                    </a>
                    <a href="{{ route('loja.carrinho', $lojaAtual->slug) }}"
                       class="hover:text-emerald-600 transition-colors">
                        Carrinho
                    </a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
