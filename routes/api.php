<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * Rotas da API REST v1 do LucraVendas.
 *
 * Prefixo: /api/v1
 * Autenticação: Laravel Sanctum (Bearer token)
 * Padrão: RESTful com API Resources em todas as respostas
 */

Route::prefix('v1')->name('api.v1.')->group(function (): void {

    // ─── Autenticação ─────────────────────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::post('register', [\App\Modules\Tenant\Presentation\Controllers\AuthController::class, 'register'])
            ->name('register');

        Route::post('login', [\App\Modules\Tenant\Presentation\Controllers\AuthController::class, 'login'])
            ->name('login');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('logout', [\App\Modules\Tenant\Presentation\Controllers\AuthController::class, 'logout'])
                ->name('logout');

            Route::get('me', [\App\Modules\Tenant\Presentation\Controllers\AuthController::class, 'me'])
                ->name('me');
        });
    });

    // ─── Rotas protegidas por autenticação Sanctum ────────────────────────────
    Route::middleware('auth:sanctum')->group(function (): void {

        // Catálogo — disponível em breve (Fase 2)
        // Route::apiResource('products', ProductController::class);
        // Route::apiResource('categories', CategoryController::class);

        // Carrinho e Checkout — disponível em breve (Fase 3)
        // Route::apiResource('cart/items', CartItemController::class);
        // Route::post('checkout', CheckoutController::class);

    });

});
