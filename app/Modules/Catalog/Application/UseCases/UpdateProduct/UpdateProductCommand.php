<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\UseCases\UpdateProduct;

/**
 * Comando de entrada para atualização de produto.
 */
final class UpdateProductCommand
{
    public function __construct(
        public readonly int $productId,
        public readonly string $tenantId,
        public readonly string $name,
        public readonly int $price,
        public readonly ?string $description = null,
        public readonly ?int $comparePrice = null,
        public readonly ?string $sku = null,
        public readonly ?int $categoryId = null,
    ) {}
}
