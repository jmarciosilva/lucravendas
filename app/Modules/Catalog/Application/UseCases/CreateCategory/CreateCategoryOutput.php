<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\UseCases\CreateCategory;

use JsonSerializable;

/**
 * DTO de saída com os dados da categoria recém-criada.
 *
 * Implementa JsonSerializable para garantir que as propriedades
 * sejam serializadas em snake_case (padrão da API), já que PHP
 * por padrão serializa propriedades camelCase sem conversão.
 */
final class CreateCategoryOutput implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $tenantId,
        public readonly ?int $parentId,
    ) {}

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'slug'      => $this->slug,
            'tenant_id' => $this->tenantId,
            'parent_id' => $this->parentId,
        ];
    }
}
