<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Repositories;

use App\Modules\Marketplace\Domain\Entities\Seller;

interface SellerRepositoryInterface
{
    public function save(Seller $seller): Seller;

    public function update(Seller $seller): void;

    public function findById(int $id): ?Seller;

    public function findBySlug(string $slug, string $tenantId): ?Seller;

    public function findByUserId(int $userId, string $tenantId): ?Seller;

    /** @return Seller[] */
    public function findActiveByTenant(string $tenantId): array;

    public function slugExistsInTenant(string $slug, string $tenantId): bool;
}
