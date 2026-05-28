<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Infrastructure\Repositories;

use App\Modules\Shipping\Domain\Entities\ShippingZone;
use App\Modules\Shipping\Domain\Repositories\ShippingZoneRepositoryInterface;
use App\Modules\Shipping\Infrastructure\Models\ShippingZoneModel;

final class EloquentShippingZoneRepository implements ShippingZoneRepositoryInterface
{
    public function save(ShippingZone $zone): ShippingZone
    {
        $model = ShippingZoneModel::create([
            'tenant_id' => $zone->tenantId(),
            'name'      => $zone->name(),
            'states'    => $zone->states(),
            'is_active' => $zone->isActive(),
        ]);

        return $this->toDomain($model);
    }

    public function findByState(string $state, string $tenantId): ?ShippingZone
    {
        // Busca zona ativa que contenha o estado no array JSON
        $model = ShippingZoneModel::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereJsonContains('states', strtoupper($state))
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    /** @return ShippingZone[] */
    public function findAllByTenant(string $tenantId): array
    {
        return ShippingZoneModel::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (ShippingZoneModel $m) => $this->toDomain($m))
            ->all();
    }

    private function toDomain(ShippingZoneModel $model): ShippingZone
    {
        return ShippingZone::restore(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            states: $model->states ?? [],
            isActive: (bool) $model->is_active,
        );
    }
}
