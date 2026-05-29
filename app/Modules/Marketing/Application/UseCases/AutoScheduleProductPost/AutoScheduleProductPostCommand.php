<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Application\UseCases\AutoScheduleProductPost;

use App\Modules\Catalog\Domain\Entities\Product;

final class AutoScheduleProductPostCommand
{
    public function __construct(
        public readonly Product $product,
        public readonly string  $tenantId,
    ) {}
}
