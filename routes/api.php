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

    // ─── Catálogo — rotas públicas (leitura sem autenticação) ────────────────
    Route::prefix('categories')->name('categories.')->group(function (): void {
        Route::get('/', [\App\Modules\Catalog\Presentation\Controllers\CategoryController::class, 'index'])
            ->name('index');
    });

    Route::prefix('products')->name('products.')->group(function (): void {
        Route::get('/', [\App\Modules\Catalog\Presentation\Controllers\ProductController::class, 'index'])
            ->name('index');
        // A rota de busca deve vir antes de {slug} para não ser capturada como parâmetro
        Route::get('/search', [\App\Modules\Catalog\Presentation\Controllers\ProductController::class, 'search'])
            ->name('search');
        Route::get('/{slug}', [\App\Modules\Catalog\Presentation\Controllers\ProductController::class, 'show'])
            ->name('show');
    });

    // ─── Catálogo e demais recursos — rotas protegidas ────────────────────────
    Route::middleware('auth:sanctum')->group(function (): void {

        // ─── Catálogo — escrita (requer tenant_admin) ─────────────────────────
        Route::prefix('categories')->name('categories.')->group(function (): void {
            Route::post('/', [\App\Modules\Catalog\Presentation\Controllers\CategoryController::class, 'store'])
                ->name('store');
        });

        Route::prefix('products')->name('products.')->group(function (): void {
            Route::post('/', [\App\Modules\Catalog\Presentation\Controllers\ProductController::class, 'store'])
                ->name('store');
            Route::put('/{id}', [\App\Modules\Catalog\Presentation\Controllers\ProductController::class, 'update'])
                ->name('update');
            Route::delete('/{id}', [\App\Modules\Catalog\Presentation\Controllers\ProductController::class, 'destroy'])
                ->name('destroy');
            Route::post('/{id}/images', [\App\Modules\Catalog\Presentation\Controllers\ProductImageController::class, 'store'])
                ->name('images.store');
        });

        // ─── Carrinho e Checkout — disponível em breve (Fase 3) ──────────────
        // Route::apiResource('cart/items', CartItemController::class);
        // Route::post('checkout', CheckoutController::class);

    });

});
