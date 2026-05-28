<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\UseCases\CreateCategory;

/**
 * Comando de entrada para criação de uma categoria.
 * Carrega apenas dados primitivos — sem dependências de infraestrutura.
 */
final class CreateCategoryCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $tenantId,
        public readonly ?int $parentId = null,
        public readonly int $sortOrder = 0,
    ) {}
}
