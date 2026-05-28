<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Domain\ValueObjects;

/**
 * Opção de frete retornada pelo cálculo (internal ou gateway externo).
 *
 * Identificadores:
 *   - "internal_{rate_id}" → tarifa da tabela interna do tenant
 *   - "me_{service_id}"    → serviço calculado pelo Melhor Envio
 */
final class ShippingOption
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $carrier,
        public readonly string $serviceCode,
        public readonly int    $priceCentavos,
        public readonly int    $minDays,
        public readonly int    $maxDays,
        public readonly bool   $isFreeShipping,
    ) {}

    public function isInternal(): bool
    {
        return str_starts_with($this->id, 'internal_');
    }

    public function internalRateId(): ?int
    {
        if (! $this->isInternal()) {
            return null;
        }

        return (int) substr($this->id, strlen('internal_'));
    }
}
