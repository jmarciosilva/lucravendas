<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Application\UseCases\ConnectAccount;

final class ConnectAccountCommand
{
    public function __construct(
        public readonly string $tenantId,
        public readonly int    $userId,
        public readonly string $platform,
        public readonly string $code,
    ) {}
}
