@extends('storefront.layouts.loja')

@section('title', 'Entrar')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2 text-center">Entrar na sua conta</h1>
            <p class="text-sm text-gray-500 text-center mb-8">
                Acesse sua conta em <span class="font-medium text-gray-700">{{ $lojaAtual->name }}</span>
            </p>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mb-6">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('loja.login.post', $lojaAtual->slug) }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('email') border-red-400 @enderror">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <input type="password"
                           id="password"
                           name="password"
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>

                <button type="submit"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-xl transition-colors">
                    Entrar
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Não tem conta?
                <a href="{{ route('loja.registro', $lojaAtual->slug) }}"
                   class="text-emerald-600 hover:text-emerald-700 font-medium transition-colors">
                    Cadastre-se
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
