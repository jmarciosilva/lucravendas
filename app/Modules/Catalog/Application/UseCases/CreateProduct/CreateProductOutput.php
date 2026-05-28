<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\UseCases\CreateProduct;

/**
 * DTO de saída com os dados do produto recém-criado.
 */
final class CreateProductOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly int $price,
        public readonly ?int $comparePrice,
        public readonly ?string $sku,
        public readonly int $stock,
        public readonly string $status,
        public readonly string $tenantId,
        public readonly ?int $categoryId,
    ) {}
}
