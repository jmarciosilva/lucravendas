<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Domain\Repositories;

use App\Modules\Shipping\Domain\Entities\ShippingRate;

interface ShippingRateRepositoryInterface
{
    public function save(ShippingRate $rate): ShippingRate;

    public function findById(int $id): ?ShippingRate;

    /** @return ShippingRate[] */
    public function findActiveByZone(int $zoneId): array;

    /** @return ShippingRate[] */
    public function findAllByTenant(string $tenantId): array;
}
