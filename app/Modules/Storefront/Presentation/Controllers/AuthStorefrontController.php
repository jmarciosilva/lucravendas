<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Controllers;

use App\Models\User;
use App\Support\StorefrontContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class AuthStorefrontController
{
    public function showLogin(string $tenantSlug): View
    {
        return view('storefront.auth.login');
    }

    public function login(Request $request, string $tenantSlug): RedirectResponse
    {
        $credenciais = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $tenantId = StorefrontContext::tenantId();

        // Autentica apenas usuários do tenant correto
        if (Auth::attempt(array_merge($credenciais, ['tenant_id' => $tenantId]))) {
            $request->session()->regenerate();
            return redirect()->route('loja.home', $tenantSlug);
        }

        return back()->withErrors([
            'email' => 'E-mail ou senha incorretos.',
        ])->onlyInput('email');
    }

    public function showRegistro(string $tenantSlug): View
    {
        return view('storefront.auth.registro');
    }

    public function registro(Request $request, string $tenantSlug): RedirectResponse
    {
        $dados = $request->validate([
            'name'                  => ['required', 'string', 'max:150'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'password'              => ['required', 'min:8', 'confirmed'],
            'phone'                 => ['nullable', 'string', 'max:20'],
        ]);

        $tenantId = StorefrontContext::tenantId();

        $user = User::create([
            'name'      => $dados['name'],
            'email'     => $dados['email'],
            'password'  => Hash::make($dados['password']),
            'phone'     => $dados['phone'] ?? null,
            'tenant_id' => $tenantId,
            'status'    => 'active',
        ]);

        $user->assignRole('customer');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('loja.home', $tenantSlug);
    }

    public function logout(Request $request, string $tenantSlug): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('loja.home', $tenantSlug);
    }
}
