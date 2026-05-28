<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\GetSellerProfile;

final class GetSellerProfileCommand
{
    public function __construct(
        public readonly string $slug,
        public readonly string $tenantId,
    ) {}
}
