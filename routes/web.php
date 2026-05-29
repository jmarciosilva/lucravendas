<?php

declare(strict_types=1);

use App\Modules\Storefront\Presentation\Controllers\AuthStorefrontController;
use App\Modules\Storefront\Presentation\Controllers\CarrinhoController;
use App\Modules\Storefront\Presentation\Controllers\CatalogoController;
use App\Modules\Storefront\Presentation\Controllers\CheckoutController;
use App\Modules\Storefront\Presentation\Controllers\HomeController;
use App\Modules\Storefront\Presentation\Controllers\MinhaContaController;
use Illuminate\Support\Facades\Route;

/*
 * Rotas da Vitrine do Cliente — LucraVendas Storefront
 *
 * Prefixo: /loja/{tenantSlug}/
 * Tenant identificado pelo slug via middleware IdentificarTenantPorSlug.
 * Auth: guard 'web' (sessão PHP), carrinho anônimo via session('cart_session_id').
 */

Route::get('/', fn () => view('welcome'));

Route::prefix('loja/{tenantSlug}')
    ->name('loja.')
    ->middleware('storefront.tenant')
    ->group(function (): void {

        // ─── Loja pública (sem autenticação) ─────────────────────────────────
        Route::get('/', HomeController::class)->name('home');

        Route::prefix('produtos')->name('produtos.')->group(function (): void {
            Route::get('/', [CatalogoController::class, 'index'])->name('index');
            Route::get('/{slug}', [CatalogoController::class, 'show'])->name('show');
        });

        Route::get('/carrinho', CarrinhoController::class)->name('carrinho');

        // ─── Autenticação scoped ao tenant ────────────────────────────────────
        Route::middleware('guest')->group(function (): void {
            Route::get('/login', [AuthStorefrontController::class, 'showLogin'])->name('login');
            Route::post('/login', [AuthStorefrontController::class, 'login'])->name('login.post');
            Route::get('/cadastro', [AuthStorefrontController::class, 'showRegistro'])->name('registro');
            Route::post('/cadastro', [AuthStorefrontController::class, 'registro'])->name('registro.post');
        });

        Route::post('/logout', [AuthStorefrontController::class, 'logout'])
            ->middleware('auth')
            ->name('logout');

        // ─── Checkout (requer autenticação) ───────────────────────────────────
        Route::middleware('auth')->group(function (): void {
            Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
            Route::get('/checkout/confirmacao/{orderId}', [CheckoutController::class, 'confirmacao'])
                ->name('confirmacao');
        });

        // ─── Área do cliente (requer autenticação) ────────────────────────────
        Route::middleware('auth')
            ->prefix('minha-conta')
            ->name('conta.')
            ->group(function (): void {
                Route::get('/', [MinhaContaController::class, 'index'])->name('index');
                Route::get('/pedidos', [MinhaContaController::class, 'pedidos'])->name('pedidos');
                Route::get('/pedidos/{id}', [MinhaContaController::class, 'pedido'])->name('pedido');
            });
    });
