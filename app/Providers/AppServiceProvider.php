<?php

declare(strict_types=1);

namespace App\Providers;

use App\Jobs\SendOrderConfirmationEmail;
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
use App\Modules\Marketplace\Application\UseCases\ApproveSeller\ApproveSellerHandler;
use App\Modules\Marketplace\Application\UseCases\CalculateCommissions\CommissionCalculatorService;
use App\Modules\Marketplace\Application\UseCases\GetSellerDashboard\GetSellerDashboardHandler;
use App\Modules\Marketplace\Application\UseCases\GetSellerProfile\GetSellerProfileHandler;
use App\Modules\Marketplace\Application\UseCases\ListSellers\ListSellersHandler;
use App\Modules\Marketplace\Application\UseCases\ProcessPayout\ProcessPayoutHandler;
use App\Modules\Marketplace\Application\UseCases\RegisterSeller\RegisterSellerHandler;
use App\Modules\Marketplace\Application\UseCases\SuspendSeller\SuspendSellerHandler;
use App\Modules\Marketplace\Domain\Repositories\CommissionRepositoryInterface;
use App\Modules\Shipping\Application\Services\InternalRateCalculator;
use App\Modules\Shipping\Application\UseCases\CalculateShipping\CalculateShippingHandler;
use App\Modules\Shipping\Application\UseCases\GenerateLabel\GenerateLabelHandler;
use App\Modules\Shipping\Application\UseCases\ProcessTrackingWebhook\ProcessTrackingWebhookHandler;
use App\Modules\Shipping\Domain\Contracts\ShippingGatewayInterface;
use App\Modules\Shipping\Domain\Repositories\ShippingRateRepositoryInterface;
use App\Modules\Shipping\Domain\Repositories\ShippingZoneRepositoryInterface;
use App\Modules\Shipping\Infrastructure\Gateways\MelhorEnvioGateway;
use App\Modules\Shipping\Infrastructure\Repositories\EloquentShippingRateRepository;
use App\Modules\Shipping\Infrastructure\Repositories\EloquentShippingZoneRepository;
use App\Modules\Marketplace\Domain\Repositories\PayoutRepositoryInterface;
use App\Modules\Marketplace\Domain\Repositories\SellerRepositoryInterface;
use App\Modules\Marketplace\Infrastructure\Repositories\EloquentCommissionRepository;
use App\Modules\Marketplace\Infrastructure\Repositories\EloquentPayoutRepository;
use App\Modules\Marketplace\Infrastructure\Repositories\EloquentSellerRepository;
use App\Modules\Orders\Application\UseCases\AddCartItem\AddCartItemHandler;
use App\Modules\Orders\Application\UseCases\ApplyCoupon\ApplyCouponHandler;
use App\Modules\Orders\Application\UseCases\Checkout\CheckoutHandler;
use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Orders\Application\UseCases\GetOrders\GetOrdersHandler;
use App\Modules\Orders\Application\UseCases\RemoveCartItem\RemoveCartItemHandler;
use App\Modules\Orders\Application\UseCases\RemoveCoupon\RemoveCouponHandler;
use App\Modules\Orders\Application\UseCases\UpdateCartItem\UpdateCartItemHandler;
use App\Modules\Orders\Domain\Events\OrderCreated;
use App\Modules\Orders\Domain\Repositories\CartRepositoryInterface;
use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Orders\Infrastructure\Repositories\EloquentCartRepository;
use App\Modules\Orders\Infrastructure\Repositories\EloquentOrderRepository;
use App\Modules\Payments\Application\UseCases\CreateBoletoPayment\CreateBoletoPaymentHandler;
use App\Modules\Payments\Application\UseCases\CreateCardPayment\CreateCardPaymentHandler;
use App\Modules\Payments\Application\UseCases\CreatePixPayment\CreatePixPaymentHandler;
use App\Modules\Payments\Application\UseCases\ProcessWebhook\ProcessWebhookHandler;
use App\Modules\Payments\Application\UseCases\RefundPayment\RefundPaymentHandler;
use App\Modules\Payments\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Catalog\Domain\Events\ProductCreated;
use App\Modules\Marketing\Application\UseCases\AutoScheduleProductPost\AutoScheduleProductPostCommand;
use App\Modules\Marketing\Application\UseCases\AutoScheduleProductPost\AutoScheduleProductPostHandler;
use App\Modules\Marketing\Application\UseCases\ConnectAccount\ConnectAccountHandler;
use App\Modules\Marketing\Application\UseCases\GetOAuthUrl\GetOAuthUrlHandler;
use App\Modules\Marketing\Application\UseCases\PublishPost\PublishPostHandler;
use App\Modules\Marketing\Application\UseCases\SchedulePost\SchedulePostHandler;
use App\Modules\Marketing\Domain\Contracts\SocialGatewayInterface;
use App\Modules\Marketing\Domain\Repositories\ScheduledPostRepositoryInterface;
use App\Modules\Marketing\Domain\Repositories\SocialAccountRepositoryInterface;
use App\Modules\Marketing\Infrastructure\Gateways\MetaGraphGateway;
use App\Modules\Marketing\Infrastructure\Repositories\EloquentScheduledPostRepository;
use App\Modules\Marketing\Infrastructure\Repositories\EloquentSocialAccountRepository;
use App\Modules\Payments\Domain\Events\PaymentApproved;
use App\Modules\Payments\Domain\Events\PaymentRejected;
use App\Modules\Payments\Domain\Repositories\PaymentTransactionRepositoryInterface;
use App\Modules\Payments\Infrastructure\Gateways\MercadoPagoGateway;
use App\Modules\Payments\Infrastructure\Repositories\EloquentPaymentTransactionRepository;
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

        // ─── Módulo Orders — binding explícito do CheckoutHandler com suas dependências ─
        // O CheckoutHandler agora depende de ShippingRateRepositoryInterface
        $this->app->bind(CheckoutHandler::class, CheckoutHandler::class);

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

        // ─── Módulo Marketplace — repositórios ────────────────────────────────
        $this->app->bind(SellerRepositoryInterface::class, EloquentSellerRepository::class);
        $this->app->bind(CommissionRepositoryInterface::class, EloquentCommissionRepository::class);
        $this->app->bind(PayoutRepositoryInterface::class, EloquentPayoutRepository::class);

        // ─── Módulo Marketplace — handlers de Use Cases ───────────────────────
        $this->app->bind(RegisterSellerHandler::class, RegisterSellerHandler::class);
        $this->app->bind(ApproveSellerHandler::class, ApproveSellerHandler::class);
        $this->app->bind(SuspendSellerHandler::class, SuspendSellerHandler::class);
        $this->app->bind(ListSellersHandler::class, ListSellersHandler::class);
        $this->app->bind(GetSellerProfileHandler::class, GetSellerProfileHandler::class);
        $this->app->bind(GetSellerDashboardHandler::class, GetSellerDashboardHandler::class);
        $this->app->bind(CommissionCalculatorService::class, CommissionCalculatorService::class);
        $this->app->bind(ProcessPayoutHandler::class, ProcessPayoutHandler::class);

        // ─── Módulo Shipping — repositórios e gateway ─────────────────────────
        $this->app->bind(ShippingGatewayInterface::class, MelhorEnvioGateway::class);
        $this->app->bind(ShippingZoneRepositoryInterface::class, EloquentShippingZoneRepository::class);
        $this->app->bind(ShippingRateRepositoryInterface::class, EloquentShippingRateRepository::class);

        // ─── Módulo Shipping — handlers de Use Cases ──────────────────────────
        $this->app->bind(InternalRateCalculator::class, InternalRateCalculator::class);
        $this->app->bind(CalculateShippingHandler::class, CalculateShippingHandler::class);
        $this->app->bind(GenerateLabelHandler::class, GenerateLabelHandler::class);
        $this->app->bind(ProcessTrackingWebhookHandler::class, ProcessTrackingWebhookHandler::class);

        // ─── Módulo Marketing — gateway, repositórios e handlers ──────────────
        $this->app->bind(SocialGatewayInterface::class, MetaGraphGateway::class);
        $this->app->bind(SocialAccountRepositoryInterface::class, EloquentSocialAccountRepository::class);
        $this->app->bind(ScheduledPostRepositoryInterface::class, EloquentScheduledPostRepository::class);
        $this->app->bind(GetOAuthUrlHandler::class, GetOAuthUrlHandler::class);
        $this->app->bind(ConnectAccountHandler::class, ConnectAccountHandler::class);
        $this->app->bind(SchedulePostHandler::class, SchedulePostHandler::class);
        $this->app->bind(PublishPostHandler::class, PublishPostHandler::class);
        $this->app->bind(AutoScheduleProductPostHandler::class, AutoScheduleProductPostHandler::class);
    }

    public function boot(): void
    {
        // Listener: OrderCreated → envia e-mail de confirmação
        Event::listen(OrderCreated::class, function (OrderCreated $event): void {
            dispatch(new SendOrderConfirmationEmail($event));
        });

        // Listener: PaymentRejected → cancela comissões pendentes do pedido
        Event::listen(PaymentRejected::class, function (PaymentRejected $event): void {
            app(CommissionRepositoryInterface::class)->cancelByOrderId($event->orderId);
        });

        // Listener: PaymentApproved → gera etiqueta de envio via Melhor Envio
        Event::listen(PaymentApproved::class, function (PaymentApproved $event): void {
            app(GenerateLabelHandler::class)->handle(
                new \App\Modules\Shipping\Application\UseCases\GenerateLabel\GenerateLabelCommand($event->orderId)
            );
        });

        // Listener: ProductCreated → agenda post automático em todas as contas sociais ativas
        Event::listen(ProductCreated::class, function (ProductCreated $event): void {
            if (config('marketing.auto_post_on_product_created')) {
                app(AutoScheduleProductPostHandler::class)->handle(
                    new AutoScheduleProductPostCommand($event->product, $event->product->tenantId())
                );
            }
        });
    }
}
