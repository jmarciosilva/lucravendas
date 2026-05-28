<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Domain\Events;

use App\Modules\Catalog\Domain\Entities\Product;

/**
 * Evento de domínio disparado quando um produto é atualizado ou publicado.
 *
 * Permite que listeners sincronizem o índice de busca e outros sistemas
 * dependentes sem que o domínio precise conhecê-los diretamente.
 */
final class ProductUpdated
{
    public function __construct(
        public readonly Product $product,
    ) {}
}
