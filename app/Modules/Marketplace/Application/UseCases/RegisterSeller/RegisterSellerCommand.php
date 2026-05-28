<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\RegisterSeller;

final class RegisterSellerCommand
{
    public function __construct(
        public readonly string  $tenantId,
        public readonly int     $userId,
        public readonly string  $name,
        public readonly string  $slug,
        public readonly ?string $description,
        public readonly array   $bankInfo,
    ) {}
}
