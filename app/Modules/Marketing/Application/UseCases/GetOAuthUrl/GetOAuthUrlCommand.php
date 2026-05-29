<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Application\UseCases\GetOAuthUrl;

final class GetOAuthUrlCommand
{
    public function __construct(
        public readonly string $platform,
        public readonly string $tenantId,
    ) {}
}
