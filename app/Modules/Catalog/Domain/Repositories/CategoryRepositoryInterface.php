<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Domain\Repositories;

use App\Modules\Catalog\Domain\Entities\Category;

/**
 * Contrato do repositório de categorias.
 *
 * Define as operações de persistência sem vazar detalhes de infraestrutura
 * para o domínio. A implementação concreta (Eloquent) fica na camada de
 * infraestrutura e é injetada via contêiner de serviços do Laravel.
 */
interface CategoryRepositoryInterface
{
    /** Persiste uma nova categoria ou atualiza uma existente */
    public function save(Category $category): void;

    /** Busca categoria por ID dentro de um tenant específico */
    public function findById(int $id, string $tenantId): ?Category;

    /** Busca categoria por slug dentro de um tenant específico */
    public function findBySlug(string $slug, string $tenantId): ?Category;

    /**
     * Retorna todas as categorias ativas do tenant como árvore hierárquica.
     *
     * @return list<Category>
     */
    public function findAllByTenant(string $tenantId): array;

    /** Verifica se já existe uma categoria com o slug informado neste tenant */
    public function existsBySlug(string $slug, string $tenantId): bool;
}
