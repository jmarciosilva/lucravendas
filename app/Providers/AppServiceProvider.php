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
use App\Modules\Tenant\Application\UseCases\LoginUser\LoginUserHandler;
use App\Modules\Tenant\Application\UseCases\RegisterUser\RegisterUserHandler;
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
    }

    public function boot(): void {}
}
