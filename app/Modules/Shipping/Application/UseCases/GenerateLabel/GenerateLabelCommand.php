<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Application\UseCases\GenerateLabel;

final class GenerateLabelCommand
{
    public function __construct(
        public readonly int $orderId,
    ) {}
}
