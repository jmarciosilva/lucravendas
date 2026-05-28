<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Domain\Repositories;

use App\Modules\Shipping\Domain\Entities\ShippingZone;

interface ShippingZoneRepositoryInterface
{
    public function save(ShippingZone $zone): ShippingZone;

    /** Retorna a primeira zona ativa que cobre o estado informado */
    public function findByState(string $state, string $tenantId): ?ShippingZone;

    /** @return ShippingZone[] */
    public function findAllByTenant(string $tenantId): array;
}
