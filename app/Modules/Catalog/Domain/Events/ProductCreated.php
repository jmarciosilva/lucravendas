<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Domain\Events;

use App\Modules\Catalog\Domain\Entities\Product;

/**
 * Evento de domínio disparado quando um novo produto é criado.
 *
 * Listeners podem reagir a este evento para, por exemplo,
 * indexar o produto no Meilisearch ou gerar um post de marketing.
 */
final class ProductCreated
{
    public function __construct(
        public readonly Product $product,
    ) {}
}
