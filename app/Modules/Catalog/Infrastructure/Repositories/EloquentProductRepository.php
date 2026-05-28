<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Repositories;

use App\Modules\Catalog\Domain\Entities\Product;
use App\Modules\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Catalog\Domain\ValueObjects\ProductStatus;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;

/**
 * Implementação Eloquent do repositório de produtos.
 *
 * Realiza a tradução bidirecional entre a entidade de domínio Product
 * e o modelo Eloquent ProductModel. Encapsula toda a complexidade
 * de queries, filtros e paginação do Eloquent.
 */
class EloquentProductRepository implements ProductRepositoryInterface
{
    public function save(Product $product): void
    {
        ProductModel::updateOrCreate(
            ['id' => $product->id()],
            [
                'name'          => $product->name(),
                'slug'          => $product->slug(),
                'description'   => $product->description(),
                'price'         => $product->price()->centavos(),
                'compare_price' => $product->comparePrice()?->centavos(),
                'sku'           => $product->sku(),
                'stock'         => $product->stock(),
                'status'        => $product->status()->value(),
                'tenant_id'     => $product->tenantId(),
                'category_id'   => $product->categoryId(),
            ]
        );
    }

    public function findById(int $id, string $tenantId): ?Product
    {
        $model = ProductModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug, string $tenantId): ?Product
    {
        $model = ProductModel::query()
            ->where('slug', $slug)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function paginate(string $tenantId, array $filters = [], int $perPage = 15, int $page = 1): array
    {
        $query = ProductModel::query()->where('tenant_id', $tenantId);

        // Filtro de status (padrão: exibe apenas produtos ativos na API pública)
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', 'active');
        }

        // Filtro por categoria
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filtro de preço mínimo (em centavos)
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', (int) $filters['min_price']);
        }

        // Filtro de preço máximo (em centavos)
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', (int) $filters['max_price']);
        }

        $paginator = $query
            ->orderBy('name')
            ->paginate(perPage: $perPage, page: $page);

        return [
            'data'         => array_map(
                fn (ProductModel $model) => $this->toEntity($model),
                $paginator->items()
            ),
            'total'        => $paginator->total(),
            'per_page'     => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ];
    }

    public function delete(int $id, string $tenantId): void
    {
        ProductModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();
    }

    public function existsBySlug(string $slug, string $tenantId, ?int $excludeId = null): bool
    {
        $query = ProductModel::query()
            ->where('slug', $slug)
            ->where('tenant_id', $tenantId);

        // Exclui o próprio produto ao validar durante uma atualização
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function findLowStock(string $tenantId, int $threshold = 5): array
    {
        return ProductModel::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('stock', '<', $threshold)
            ->orderBy('stock')
            ->get()
            ->map(fn (ProductModel $model) => $this->toEntity($model))
            ->all();
    }

    /** Hidrata a entidade de domínio a partir do modelo Eloquent */
    private function toEntity(ProductModel $model): Product
    {
        return Product::restore(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            price: Money::fromCentavos($model->price),
            comparePrice: $model->compare_price !== null
                ? Money::fromCentavos($model->compare_price)
                : null,
            sku: $model->sku,
            stock: $model->stock,
            status: ProductStatus::fromString($model->status),
            tenantId: $model->tenant_id,
            categoryId: $model->category_id,
        );
    }
}
