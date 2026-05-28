<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Catalog\Application\UseCases\CreateCategory\CreateCategoryHandler;
use App\Modules\Catalog\Application\UseCases\CreateProduct\CreateProductHandler;
use App\Modules\Catalog\Application\UseCases\DeleteProduct\DeleteProductHandler;
use App\Modules\Catalog\Application\UseCases\ListCategories\ListCategoriesHandler;
use App\Modules\Catalog\Application\UseCases\UpdateProduct\UpdateProductHandler;
use App\Modules\Catalog\Application\UseCases\UploadProductImage\UploadProductImageHandler;
use App\Modules\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Modules\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Modules\Catalog\Infrastructure\Repositories\EloquentCategoryRepository;
use App\Modules\Catalog\Infrastructure\Repositories\EloquentProductRepository;
use App\Jobs\SendOrderConfirmationEmail;
use App\Modules\Orders\Application\UseCases\AddCartItem\AddCartItemHandler;
use App\Modules\Orders\Domain\Events\OrderCreated;
use App\Modules\Payments\Application\UseCases\CreateBoletoPayment\CreateBoletoPaymentHandler;
use App\Modules\Payments\Application\UseCases\CreateCardPayment\CreateCardPaymentHandler;
use App\Modules\Payments\Application\UseCases\CreatePixPayment\CreatePixPaymentHandler;
use App\Modules\Payments\Application\UseCases\ProcessWebhook\ProcessWebhookHandler;
use App\Modules\Payments\Application\UseCases\RefundPayment\RefundPaymentHandler;
use App\Modules\Payments\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Domain\Repositories\PaymentTransactionRepositoryInterface;
use App\Modules\Payments\Infrastructure\Gateways\MercadoPagoGateway;
use App\Modules\Payments\Infrastructure\Repositories\EloquentPaymentTransactionRepository;
use App\Modules\Orders\Application\UseCases\ApplyCoupon\ApplyCouponHandler;
use App\Modules\Orders\Application\UseCases\Checkout\CheckoutHandler;
use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Orders\Application\UseCases\GetOrders\GetOrdersHandler;
use App\Modules\Orders\Application\UseCases\RemoveCartItem\RemoveCartItemHandler;
use App\Modules\Orders\Application\UseCases\RemoveCoupon\RemoveCouponHandler;
use App\Modules\Orders\Application\UseCases\UpdateCartItem\UpdateCartItemHandler;
use App\Modules\Orders\Domain\Repositories\CartRepositoryInterface;
use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Orders\Infrastructure\Repositories\EloquentCartRepository;
use App\Modules\Orders\Infrastructure\Repositories\EloquentOrderRepository;
use App\Modules\Tenant\Application\UseCases\LoginUser\LoginUserHandler;
use App\Modules\Tenant\Application\UseCases\RegisterUser\RegisterUserHandler;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Provider principal da aplicação.
 *
 * Registra bindings explícitos dos handlers de Use Cases e das interfaces
 * de repositório para que o container de IoC do Laravel resolva as dependências
 * corretamente via injeção nos controllers e handlers.
 */
final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ─── Módulo Tenant ────────────────────────────────────────────────────
        $this->app->bind(RegisterUserHandler::class, RegisterUserHandler::class);
        $this->app->bind(LoginUserHandler::class, LoginUserHandler::class);

        // ─── Módulo Catalog — repositórios (interface → implementação Eloquent) ─
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);

        // ─── Módulo Catalog — handlers de Use Cases ───────────────────────────
        $this->app->bind(ListCategoriesHandler::class, ListCategoriesHandler::class);
        $this->app->bind(CreateCategoryHandler::class, CreateCategoryHandler::class);
        $this->app->bind(CreateProductHandler::class, CreateProductHandler::class);
        $this->app->bind(UpdateProductHandler::class, UpdateProductHandler::class);
        $this->app->bind(DeleteProductHandler::class, DeleteProductHandler::class);
        $this->app->bind(UploadProductImageHandler::class, UploadProductImageHandler::class);

        // ─── Módulo Orders — repositórios ─────────────────────────────────────
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);

        // ─── Módulo Orders — handlers de Use Cases ────────────────────────────
        $this->app->bind(GetCartHandler::class, GetCartHandler::class);
        $this->app->bind(AddCartItemHandler::class, AddCartItemHandler::class);
        $this->app->bind(UpdateCartItemHandler::class, UpdateCartItemHandler::class);
        $this->app->bind(RemoveCartItemHandler::class, RemoveCartItemHandler::class);
        $this->app->bind(ApplyCouponHandler::class, ApplyCouponHandler::class);
        $this->app->bind(RemoveCouponHandler::class, RemoveCouponHandler::class);
        $this->app->bind(CheckoutHandler::class, CheckoutHandler::class);
        $this->app->bind(GetOrdersHandler::class, GetOrdersHandler::class);

        // ─── Módulo Payments — gateway e repositório ──────────────────────────
        $this->app->bind(PaymentGatewayInterface::class, MercadoPagoGateway::class);
        $this->app->bind(PaymentTransactionRepositoryInterface::class, EloquentPaymentTransactionRepository::class);

        // ─── Módulo Payments — handlers de Use Cases ──────────────────────────
        $this->app->bind(CreatePixPaymentHandler::class, CreatePixPaymentHandler::class);
        $this->app->bind(CreateCardPaymentHandler::class, CreateCardPaymentHandler::class);
        $this->app->bind(CreateBoletoPaymentHandler::class, CreateBoletoPaymentHandler::class);
        $this->app->bind(ProcessWebhookHandler::class, ProcessWebhookHandler::class);
        $this->app->bind(RefundPaymentHandler::class, RefundPaymentHandler::class);
    }

    public function boot(): void
    {
        // Listener: OrderCreated → envia e-mail de confirmação
        Event::listen(OrderCreated::class, function (OrderCreated $event): void {
            dispatch(new SendOrderConfirmationEmail($event));
        });
    }
}
