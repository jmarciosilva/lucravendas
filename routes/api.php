<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * Rotas da API REST v1 do LucraVendas.
 *
 * Prefixo: /api/v1
 * Autenticação: Laravel Sanctum (Bearer token)
 * Padrão: RESTful com API Resources em todas as respostas
 *
 * Rate Limiting:
 *   throttle:auth     → 10 req/min por IP  (login, register)
 *   throttle:api      → 60 req/min por IP  (endpoints públicos)
 *   throttle:api-auth → 1000 req/min por user (endpoints autenticados)
 */

Route::prefix('v1')->name('api.v1.')->group(function (): void {

    // ─── Autenticação ─────────────────────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function (): void {
        // Endpoints sensíveis: limitados a 10 req/min por IP
        Route::middleware('throttle:auth')->group(function (): void {
            Route::post('register', [\App\Modules\Tenant\Presentation\Controllers\AuthController::class, 'register'])
                ->name('register');
            Route::post('login', [\App\Modules\Tenant\Presentation\Controllers\AuthController::class, 'login'])
                ->name('login');
        });

        Route::middleware(['auth:sanctum', 'throttle:api-auth'])->group(function (): void {
            Route::post('logout', [\App\Modules\Tenant\Presentation\Controllers\AuthController::class, 'logout'])
                ->name('logout');
            Route::get('me', [\App\Modules\Tenant\Presentation\Controllers\AuthController::class, 'me'])
                ->name('me');
        });
    });

    // ─── Catálogo — rotas públicas (leitura sem autenticação) ────────────────
    Route::middleware('throttle:api')->group(function (): void {
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
    });

    // ─── Catálogo e demais recursos — rotas protegidas ────────────────────────
    Route::middleware(['auth:sanctum', 'throttle:api-auth'])->group(function (): void {

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

        // ─── Pedidos — histórico e detalhe ────────────────────────────────────
        Route::prefix('orders')->name('orders.')->group(function (): void {
            Route::get('/', [\App\Modules\Orders\Presentation\Controllers\OrderController::class, 'index'])
                ->name('index');
            Route::get('/{id}', [\App\Modules\Orders\Presentation\Controllers\OrderController::class, 'show'])
                ->name('show');
        });

        // ─── Checkout ─────────────────────────────────────────────────────────
        Route::post('checkout', [\App\Modules\Orders\Presentation\Controllers\OrderController::class, 'checkout'])
            ->name('checkout');
    });

    // ─── Pagamentos via Mercado Pago (requer autenticação) ───────────────────
    Route::prefix('payments')->name('payments.')->middleware(['auth:sanctum', 'throttle:api-auth'])->group(function (): void {
        Route::post('/pix', [\App\Modules\Payments\Presentation\Controllers\PaymentController::class, 'pix'])
            ->name('pix');
        Route::post('/card', [\App\Modules\Payments\Presentation\Controllers\PaymentController::class, 'card'])
            ->name('card');
        Route::post('/boleto', [\App\Modules\Payments\Presentation\Controllers\PaymentController::class, 'boleto'])
            ->name('boleto');
        Route::get('/{orderId}/status', [\App\Modules\Payments\Presentation\Controllers\PaymentController::class, 'status'])
            ->name('status');
    });

    // ─── Frete — cálculo público (carrinho anônimo ou autenticado) ──────────
    Route::get('/shipping/calculate', [\App\Modules\Shipping\Presentation\Controllers\ShippingController::class, 'calculate'])
        ->middleware('throttle:api')
        ->name('shipping.calculate');

    // ─── Webhooks — sem rate limiting (chamados por serviços externos) ───────
    Route::post('/webhooks/shipping', \App\Modules\Shipping\Presentation\Controllers\TrackingWebhookController::class)
        ->name('webhooks.shipping');
    Route::post('/webhooks/mercadopago', [\App\Modules\Payments\Presentation\Controllers\WebhookController::class, 'mercadopago'])
        ->name('webhooks.mercadopago');

    // ─── Marketplace — rotas públicas (leitura sem autenticação) ────────────
    Route::prefix('marketplace/sellers')->name('marketplace.sellers.')->middleware('throttle:api')->group(function (): void {
        Route::get('/', [\App\Modules\Marketplace\Presentation\Controllers\SellerController::class, 'index'])
            ->name('index');
        Route::get('/{slug}', [\App\Modules\Marketplace\Presentation\Controllers\SellerController::class, 'show'])
            ->name('show');
        Route::get('/{slug}/products', [\App\Modules\Marketplace\Presentation\Controllers\SellerController::class, 'products'])
            ->name('products');
    });

    // ─── Marketplace — rotas protegidas (requer autenticação) ────────────────
    Route::middleware(['auth:sanctum', 'throttle:api-auth'])->group(function (): void {
        Route::post('sellers/register', [\App\Modules\Marketplace\Presentation\Controllers\SellerController::class, 'register'])
            ->name('sellers.register');
        Route::get('seller/dashboard', [\App\Modules\Marketplace\Presentation\Controllers\SellerController::class, 'dashboard'])
            ->name('seller.dashboard');
    });

    // ─── Marketing — contas sociais e posts agendados ────────────────────────
    Route::prefix('marketing')->name('marketing.')->group(function (): void {
        // Pública: callback OAuth do Facebook/Instagram (sem auth, recebe redirect do Facebook)
        Route::get('oauth/callback', [\App\Modules\Marketing\Presentation\Controllers\MarketingController::class, 'oauthCallback'])
            ->name('oauth.callback');

        Route::middleware(['auth:sanctum', 'throttle:api-auth'])->group(function (): void {
            Route::get('connect/{platform}', [\App\Modules\Marketing\Presentation\Controllers\MarketingController::class, 'oauthUrl'])
                ->name('connect');
            Route::get('accounts', [\App\Modules\Marketing\Presentation\Controllers\MarketingController::class, 'accounts'])
                ->name('accounts.index');
            Route::get('posts', [\App\Modules\Marketing\Presentation\Controllers\MarketingController::class, 'listPosts'])
                ->name('posts.index');
            Route::post('posts', [\App\Modules\Marketing\Presentation\Controllers\MarketingController::class, 'schedulePost'])
                ->name('posts.store');
        });
    });

    // ─── Carrinho — público (anônimo via X-Cart-Session ou autenticado) ───────
    Route::prefix('cart')->name('cart.')->middleware('throttle:api')->group(function (): void {
        Route::get('/', [\App\Modules\Orders\Presentation\Controllers\CartController::class, 'show'])
            ->name('show');
        Route::post('/items', [\App\Modules\Orders\Presentation\Controllers\CartController::class, 'addItem'])
            ->name('items.store');
        Route::put('/items/{id}', [\App\Modules\Orders\Presentation\Controllers\CartController::class, 'updateItem'])
            ->name('items.update');
        Route::delete('/items/{id}', [\App\Modules\Orders\Presentation\Controllers\CartController::class, 'removeItem'])
            ->name('items.destroy');
        Route::post('/coupon', [\App\Modules\Orders\Presentation\Controllers\CartController::class, 'applyCoupon'])
            ->name('coupon.apply');
        Route::delete('/coupon', [\App\Modules\Orders\Presentation\Controllers\CartController::class, 'removeCoupon'])
            ->name('coupon.remove');
    });

});
