<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Application\Services;

use App\Modules\Shipping\Domain\Repositories\ShippingRateRepositoryInterface;
use App\Modules\Shipping\Domain\Repositories\ShippingZoneRepositoryInterface;
use App\Modules\Shipping\Domain\ValueObjects\ShippingOption;

/**
 * Calcula opções de frete usando o sistema de zonas e tarifas internas do tenant.
 *
 * Não depende de API externa — funciona como fallback ou como método principal
 * quando o tenant não tem integração com o Melhor Envio configurada.
 */
final class InternalRateCalculator
{
    public function __construct(
        private readonly ShippingZoneRepositoryInterface $zoneRepository,
        private readonly ShippingRateRepositoryInterface $rateRepository,
    ) {}

    /**
     * Retorna as opções de frete disponíveis para um estado e peso.
     *
     * @return ShippingOption[]
     */
    public function calculate(
        string $state,
        string $tenantId,
        int    $totalWeightGrams,
        int    $subtotalCentavos,
    ): array {
        $zone = $this->zoneRepository->findByState($state, $tenantId);

        if ($zone === null) {
            return [];
        }

        $rates = $this->rateRepository->findActiveByZone($zone->id());

        return array_map(function ($rate) use ($totalWeightGrams, $subtotalCentavos) {
            $isFree       = $rate->isFreeShippingFor($subtotalCentavos);
            $priceCentavos = $isFree ? 0 : $rate->calculatePrice($totalWeightGrams, $subtotalCentavos);

            return new ShippingOption(
                id: 'internal_' . $rate->id(),
                name: $rate->name(),
                carrier: $rate->carrier(),
                serviceCode: $rate->serviceCode() ?? '',
                priceCentavos: $priceCentavos,
                minDays: $rate->minDays(),
                maxDays: $rate->maxDays(),
                isFreeShipping: $isFree,
            );
        }, $rates);
    }
}
