<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Application\UseCases\ConnectAccount;

use JsonSerializable;

final class ConnectAccountOutput implements JsonSerializable
{
    public function __construct(
        public readonly int    $accountId,
        public readonly string $accountName,
        public readonly string $platform,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'account_id'   => $this->accountId,
            'account_name' => $this->accountName,
            'platform'     => $this->platform,
        ];
    }
}
