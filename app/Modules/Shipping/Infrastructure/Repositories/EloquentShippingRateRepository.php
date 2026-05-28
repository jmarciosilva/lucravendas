<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Infrastructure\Repositories;

use App\Modules\Shipping\Domain\Entities\ShippingRate;
use App\Modules\Shipping\Domain\Repositories\ShippingRateRepositoryInterface;
use App\Modules\Shipping\Infrastructure\Models\ShippingRateModel;

final class EloquentShippingRateRepository implements ShippingRateRepositoryInterface
{
    public function save(ShippingRate $rate): ShippingRate
    {
        $model = ShippingRateModel::create([
            'zone_id'                  => $rate->zoneId(),
            'tenant_id'                => $rate->tenantId(),
            'name'                     => $rate->name(),
            'carrier'                  => $rate->carrier(),
            'service_code'             => $rate->serviceCode(),
            'base_price'               => $rate->basePrice(),
            'price_per_kg'             => $rate->pricePerKg(),
            'min_days'                 => $rate->minDays(),
            'max_days'                 => $rate->maxDays(),
            'free_shipping_threshold'  => $rate->freeShippingThreshold(),
            'is_active'                => $rate->isActive(),
        ]);

        return $this->toDomain($model);
    }

    public function findById(int $id): ?ShippingRate
    {
        $model = ShippingRateModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    /** @return ShippingRate[] */
    public function findActiveByZone(int $zoneId): array
    {
        return ShippingRateModel::where('zone_id', $zoneId)
            ->where('is_active', true)
            ->orderBy('base_price')
            ->get()
            ->map(fn (ShippingRateModel $m) => $this->toDomain($m))
            ->all();
    }

    /** @return ShippingRate[] */
    public function findAllByTenant(string $tenantId): array
    {
        return ShippingRateModel::where('tenant_id', $tenantId)
            ->with('zone')
            ->orderBy('zone_id')
            ->orderBy('base_price')
            ->get()
            ->map(fn (ShippingRateModel $m) => $this->toDomain($m))
            ->all();
    }

    private function toDomain(ShippingRateModel $model): ShippingRate
    {
        return ShippingRate::restore(
            id: $model->id,
            zoneId: $model->zone_id,
            tenantId: $model->tenant_id,
            name: $model->name,
            carrier: $model->carrier,
            serviceCode: $model->service_code,
            basePrice: $model->base_price,
            pricePerKg: $model->price_per_kg,
            minDays: $model->min_days,
            maxDays: $model->max_days,
            freeShippingThreshold: $model->free_shipping_threshold,
            isActive: (bool) $model->is_active,
        );
    }
}
