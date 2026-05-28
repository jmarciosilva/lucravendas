<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Repositories;

use App\Modules\Catalog\Domain\Entities\Category;
use App\Modules\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Modules\Catalog\Infrastructure\Models\CategoryModel;

/**
 * Implementação Eloquent do repositório de categorias.
 *
 * Faz a tradução entre o modelo Eloquent (infraestrutura) e a entidade
 * de domínio Category. O domínio nunca toca o Eloquent diretamente.
 */
class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function save(Category $category): void
    {
        CategoryModel::updateOrCreate(
            ['id' => $category->id()],
            [
                'name'       => $category->name(),
                'slug'       => $category->slug(),
                'tenant_id'  => $category->tenantId(),
                'parent_id'  => $category->parentId(),
                'sort_order' => $category->sortOrder(),
                'is_active'  => $category->isActive(),
            ]
        );
    }

    public function findById(int $id, string $tenantId): ?Category
    {
        $model = CategoryModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug, string $tenantId): ?Category
    {
        $model = CategoryModel::query()
            ->where('slug', $slug)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findAllByTenant(string $tenantId): array
    {
        return CategoryModel::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (CategoryModel $model) => $this->toEntity($model))
            ->all();
    }

    public function existsBySlug(string $slug, string $tenantId): bool
    {
        return CategoryModel::query()
            ->where('slug', $slug)
            ->where('tenant_id', $tenantId)
            ->exists();
    }

    /** Converte o modelo Eloquent em entidade de domínio */
    private function toEntity(CategoryModel $model): Category
    {
        return Category::create(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            tenantId: $model->tenant_id,
            parentId: $model->parent_id,
            sortOrder: $model->sort_order,
        );
    }
}
