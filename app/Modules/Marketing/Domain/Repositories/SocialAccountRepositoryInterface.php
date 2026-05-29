<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Domain\Repositories;

use App\Modules\Marketing\Domain\Entities\SocialAccount;

interface SocialAccountRepositoryInterface
{
    public function save(SocialAccount $account): SocialAccount;

    public function findById(int $id, string $tenantId): ?SocialAccount;

    /** @return SocialAccount[] */
    public function findAllByTenant(string $tenantId): array;

    /** @return SocialAccount[] somente contas ativas */
    public function findActiveByTenant(string $tenantId): array;
}
