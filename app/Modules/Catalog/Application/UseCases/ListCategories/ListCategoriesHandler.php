<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\UseCases\ListCategories;

use App\Modules\Catalog\Domain\Repositories\CategoryRepositoryInterface;
use App\Modules\Catalog\Infrastructure\Models\CategoryModel;

/**
 * Retorna a árvore de categorias ativas do tenant.
 *
 * Monta a hierarquia em memória após uma única query no banco,
 * evitando o problema N+1 ao carregar pai e filhos separadamente.
 */
final class ListCategoriesHandler
{
    public function __construct(
        private readonly CategoryRepositoryInterface $repository,
    ) {}

    /**
     * Retorna as categorias do tenant organizadas em árvore hierárquica.
     *
     * @return list<array<string, mixed>>
     */
    public function handle(string $tenantId): array
    {
        // Carrega todas as categorias ativas em uma única query
        $all = CategoryModel::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Monta a árvore em memória agrupando filhos por parent_id
        $byParent = $all->groupBy('parent_id');

        return $this->buildTree($byParent, null);
    }

    /**
     * Monta recursivamente a árvore de categorias.
     *
     * @param  \Illuminate\Support\Collection<int|null, \Illuminate\Support\Collection<int, CategoryModel>>  $byParent
     * @return list<array<string, mixed>>
     */
    private function buildTree($byParent, ?int $parentId): array
    {
        $nodes = $byParent->get($parentId, collect());

        return $nodes->map(function (CategoryModel $node) use ($byParent): array {
            return [
                'id'         => $node->id,
                'name'       => $node->name,
                'slug'       => $node->slug,
                'sort_order' => $node->sort_order,
                'children'   => $this->buildTree($byParent, $node->id),
            ];
        })->values()->all();
    }
}
