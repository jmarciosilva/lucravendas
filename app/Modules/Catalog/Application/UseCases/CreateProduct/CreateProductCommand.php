<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\UseCases\CreateProduct;

/**
 * Comando de entrada para criação de produto.
 *
 * Preço e preço de comparação chegam em centavos (inteiro)
 * vindos da camada de apresentação, que os converte do JSON da requisição.
 */
final class CreateProductCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly int $price,
        public readonly string $tenantId,
        public readonly ?string $description = null,
        public readonly ?int $comparePrice = null,
        public readonly ?string $sku = null,
        public readonly int $stock = 0,
        public readonly ?int $categoryId = null,
    ) {}
}
