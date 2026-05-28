<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Domain\Entities;

final class ShippingRate
{
    private function __construct(
        private readonly ?int    $id,
        private readonly int     $zoneId,
        private readonly string  $tenantId,
        private readonly string  $name,
        private readonly string  $carrier,
        private readonly ?string $serviceCode,
        private readonly int     $basePrice,          // centavos
        private readonly int     $pricePerKg,         // centavos por kg
        private readonly int     $minDays,
        private readonly int     $maxDays,
        private readonly ?int    $freeShippingThreshold, // centavos; null = sem frete grátis
        private readonly bool    $isActive,
    ) {}

    public static function create(
        int     $zoneId,
        string  $tenantId,
        string  $name,
        string  $carrier,
        ?string $serviceCode,
        int     $basePrice,
        int     $pricePerKg,
        int     $minDays,
        int     $maxDays,
        ?int    $freeShippingThreshold,
    ): self {
        return new self(
            id: null,
            zoneId: $zoneId,
            tenantId: $tenantId,
            name: $name,
            carrier: $carrier,
            serviceCode: $serviceCode,
            basePrice: $basePrice,
            pricePerKg: $pricePerKg,
            minDays: $minDays,
            maxDays: $maxDays,
            freeShippingThreshold: $freeShippingThreshold,
            isActive: true,
        );
    }

    public static function restore(
        int     $id,
        int     $zoneId,
        string  $tenantId,
        string  $name,
        string  $carrier,
        ?string $serviceCode,
        int     $basePrice,
        int     $pricePerKg,
        int     $minDays,
        int     $maxDays,
        ?int    $freeShippingThreshold,
        bool    $isActive,
    ): self {
        return new self(
            $id, $zoneId, $tenantId, $name, $carrier, $serviceCode,
            $basePrice, $pricePerKg, $minDays, $maxDays, $freeShippingThreshold, $isActive
        );
    }

    /**
     * Calcula o preço para um dado peso.
     * Se o subtotal do pedido atingir o threshold, retorna zero (frete grátis).
     */
    public function calculatePrice(int $weightGrams, int $subtotalCentavos): int
    {
        if ($this->freeShippingThreshold !== null && $subtotalCentavos >= $this->freeShippingThreshold) {
            return 0;
        }

        $weightKg = $weightGrams / 1000;
        return (int) round($this->basePrice + ($weightKg * $this->pricePerKg));
    }

    public function isFreeShippingFor(int $subtotalCentavos): bool
    {
        return $this->freeShippingThreshold !== null && $subtotalCentavos >= $this->freeShippingThreshold;
    }

    public function id(): ?int                    { return $this->id; }
    public function zoneId(): int                 { return $this->zoneId; }
    public function tenantId(): string            { return $this->tenantId; }
    public function name(): string                { return $this->name; }
    public function carrier(): string             { return $this->carrier; }
    public function serviceCode(): ?string        { return $this->serviceCode; }
    public function basePrice(): int              { return $this->basePrice; }
    public function pricePerKg(): int             { return $this->pricePerKg; }
    public function minDays(): int                { return $this->minDays; }
    public function maxDays(): int                { return $this->maxDays; }
    public function freeShippingThreshold(): ?int { return $this->freeShippingThreshold; }
    public function isActive(): bool              { return $this->isActive; }
}
