<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\UseCases\UploadProductImage;

use Illuminate\Http\UploadedFile;

/**
 * Comando de entrada para upload de imagem de produto.
 */
final class UploadProductImageCommand
{
    public function __construct(
        public readonly int $productId,
        public readonly string $tenantId,
        public readonly UploadedFile $file,
    ) {}
}
