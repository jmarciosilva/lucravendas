<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\UseCases\CreateCategory;

use App\Modules\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Modules\Catalog\Infrastructure\Models\CategoryModel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Orquestra a criação de uma nova categoria no catálogo do tenant.
 *
 * Valida unicidade do slug por tenant antes de persistir.
 * O ID é obtido após a inserção porque categorias usam auto-increment.
 */
final class CreateCategoryHandler
{
    public function __construct(
        private readonly CategoryRepositoryInterface $repository,
    ) {}

    public function handle(CreateCategoryCommand $command): CreateCategoryOutput
    {
        return DB::transaction(function () use ($command) {
            // Verificação dentro da transação para evitar race condition de slug duplicado
            if ($this->repository->existsBySlug($command->slug, $command->tenantId)) {
                throw new RuntimeException(
                    "Já existe uma categoria com o slug '{$command->slug}' nesta loja."
                );
            }

            // Persiste diretamente via Eloquent para obter o ID gerado pelo banco
            $model = CategoryModel::create([
                'name'       => $command->name,
                'slug'       => $command->slug,
                'tenant_id'  => $command->tenantId,
                'parent_id'  => $command->parentId,
                'sort_order' => $command->sortOrder,
                'is_active'  => true,
            ]);

            return new CreateCategoryOutput(
                id: $model->id,
                name: $model->name,
                slug: $model->slug,
                tenantId: $model->tenant_id,
                parentId: $model->parent_id,
            );
        });
    }
}
