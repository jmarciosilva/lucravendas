<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Domain\Contracts;

use App\Modules\Shipping\Domain\ValueObjects\ShippingAddress;
use App\Modules\Shipping\Domain\ValueObjects\ShippingOption;

interface ShippingGatewayInterface
{
    /**
     * Calcula opções de frete em tempo real via gateway externo.
     *
     * @param array<array{weight_grams: int, length_cm: int, width_cm: int, height_cm: int}> $packages
     * @return ShippingOption[]
     */
    public function calculateRates(
        string $fromZipcode,
        string $toZipcode,
        array  $packages,
        int    $insuranceValueCentavos,
    ): array;

    /**
     * Gera etiqueta de envio e retorna os dados de rastreio e URL.
     *
     * @return array{tracking_code: string, label_url: string}
     */
    public function generateLabel(
        ShippingAddress $from,
        ShippingAddress $to,
        array           $packages,
        string          $serviceCode,
        int             $insuranceValueCentavos,
        int             $externalOrderId,
    ): array;

    /**
     * Consulta o status de rastreio de um envio pelo código de rastreio.
     */
    public function getTrackingStatus(string $trackingCode): string;
}
