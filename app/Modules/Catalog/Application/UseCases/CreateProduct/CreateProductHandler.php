<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\UseCases\CreateProduct;

use App\Modules\Catalog\Domain\Entities\Product;
use App\Modules\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Orquestra a criação de um novo produto no catálogo.
 *
 * O ID do produto é gerado pelo banco (auto-increment), por isso
 * a persistência ocorre antes da criação completa da entidade de domínio.
 */
final class CreateProductHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
        private readonly Dispatcher $events,
    ) {}

    public function handle(CreateProductCommand $command): CreateProductOutput
    {
        return DB::transaction(function () use ($command) {
            // Verificação dentro da transação para evitar race condition de slug duplicado
            if ($this->repository->existsBySlug($command->slug, $command->tenantId)) {
                throw new RuntimeException(
                    "Já existe um produto com o slug '{$command->slug}' nesta loja."
                );
            }

            // Persiste via Eloquent para obter o ID gerado pelo banco
            $model = ProductModel::create([
                'name'          => $command->name,
                'slug'          => $command->slug,
                'description'   => $command->description,
                'price'         => $command->price,
                'compare_price' => $command->comparePrice,
                'sku'           => $command->sku,
                'stock'         => $command->stock,
                'status'        => 'draft',
                'tenant_id'     => $command->tenantId,
                'category_id'   => $command->categoryId,
            ]);

            // Reconstrói a entidade de domínio com o ID real para disparar eventos
            $product = Product::create(
                id: $model->id,
                name: $command->name,
                slug: $command->slug,
                price: Money::fromCentavos($command->price),
                tenantId: $command->tenantId,
                description: $command->description,
                comparePrice: $command->comparePrice !== null
                    ? Money::fromCentavos($command->comparePrice)
                    : null,
                sku: $command->sku,
                stock: $command->stock,
                categoryId: $command->categoryId,
            );

            // Despacha eventos de domínio acumulados (ex.: ProductCreated → indexar no Scout)
            foreach ($product->pullDomainEvents() as $event) {
                $this->events->dispatch($event);
            }

            return new CreateProductOutput(
                id: $model->id,
                name: $model->name,
                slug: $model->slug,
                price: $model->price,
                comparePrice: $model->compare_price,
                sku: $model->sku,
                stock: $model->stock,
                status: $model->status,
                tenantId: $model->tenant_id,
                categoryId: $model->category_id,
            );
        });
    }
}
