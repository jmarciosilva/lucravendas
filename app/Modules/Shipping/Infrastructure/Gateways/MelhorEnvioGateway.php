<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Infrastructure\Gateways;

use App\Modules\Shipping\Domain\Contracts\ShippingGatewayInterface;
use App\Modules\Shipping\Domain\ValueObjects\ShippingAddress;
use App\Modules\Shipping\Domain\ValueObjects\ShippingOption;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Gateway de frete via Melhor Envio API v2.
 *
 * Documentação: https://docs.melhorenvio.com.br/
 * Sandbox:      https://sandbox.melhorenvio.com.br/api/v2
 * Produção:     https://www.melhorenvio.com.br/api/v2
 */
final class MelhorEnvioGateway implements ShippingGatewayInterface
{
    private string $baseUrl;
    private string $token;
    private string $fromEmail;
    private int    $insuranceCentavos;

    public function __construct()
    {
        $sandbox              = config('shipping.melhorenvio.sandbox', true);
        $this->baseUrl        = $sandbox
            ? 'https://sandbox.melhorenvio.com.br/api/v2'
            : 'https://www.melhorenvio.com.br/api/v2';
        $this->token          = config('shipping.melhorenvio.token', '');
        $this->fromEmail      = config('shipping.melhorenvio.from.email', '');
        $this->insuranceCentavos = (int) config('shipping.melhorenvio.insurance_value_centavos', 0);
    }

    /**
     * Calcula opções de frete via API do Melhor Envio.
     *
     * @param array<array{weight_grams: int, length_cm: int, width_cm: int, height_cm: int}> $packages
     * @return ShippingOption[]
     */
    public function calculateRates(
        string $fromZipcode,
        string $toZipcode,
        array  $packages,
        int    $insuranceValueCentavos,
    ): array {
        $consolidated = $this->consolidatePackages($packages);

        $payload = [
            'from'    => ['postal_code' => preg_replace('/\D/', '', $fromZipcode)],
            'to'      => ['postal_code' => preg_replace('/\D/', '', $toZipcode)],
            'package' => $consolidated,
            'options' => [
                'insurance_value' => ($insuranceValueCentavos ?: $this->insuranceCentavos) / 100,
                'receipt'         => false,
                'own_hand'        => false,
            ],
        ];

        $response = $this->http()
            ->post("{$this->baseUrl}/me/shipment/calculate", $payload);

        if ($response->serverError()) {
            throw new RuntimeException('Erro ao consultar frete no Melhor Envio.');
        }

        $rates = collect($response->json())
            ->filter(fn ($r) => isset($r['price']) && $r['price'] !== null && ! isset($r['error']));

        return $rates->map(fn (array $r) => new ShippingOption(
            id: 'me_' . $r['id'],
            name: $r['name'] . ' — ' . ($r['company']['name'] ?? 'Melhor Envio'),
            carrier: 'melhorenvio',
            serviceCode: (string) $r['id'],
            priceCentavos: (int) round((float) $r['price'] * 100),
            minDays: (int) ($r['delivery_time'] ?? $r['delivery_range']['min'] ?? 1),
            maxDays: (int) ($r['delivery_time'] ?? $r['delivery_range']['max'] ?? 10),
            isFreeShipping: false,
        ))->values()->all();
    }

    /**
     * Gera etiqueta de envio no Melhor Envio.
     *
     * Fluxo: adicionar ao carrinho → checkout → gerar → imprimir (URL do PDF).
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
    ): array {
        // 1. Adiciona envio ao carrinho ME
        $cartResponse = $this->http()->post("{$this->baseUrl}/me/cart", [
            'service'  => $serviceCode,
            'from'     => $this->formatAddress($from, config('shipping.melhorenvio.from')),
            'to'       => $this->formatAddress($to),
            'package'  => $this->consolidatePackages($packages),
            'options'  => [
                'insurance_value' => $insuranceValueCentavos / 100,
                'receipt'         => false,
                'own_hand'        => false,
            ],
            'tag'      => "order_{$externalOrderId}",
        ]);

        if ($cartResponse->failed()) {
            throw new RuntimeException(
                'Erro ao criar envio no Melhor Envio: ' . $cartResponse->body()
            );
        }

        $orderId = $cartResponse->json('id');

        // 2. Checkout (compra a etiqueta)
        $checkoutResponse = $this->http()->post("{$this->baseUrl}/me/shipment/checkout", [
            'orders' => [$orderId],
        ]);

        if ($checkoutResponse->failed()) {
            throw new RuntimeException('Erro no checkout da etiqueta no Melhor Envio.');
        }

        // 3. Gera a etiqueta
        $generateResponse = $this->http()->post("{$this->baseUrl}/me/shipment/generate", [
            'orders' => [$orderId],
        ]);

        if ($generateResponse->failed()) {
            throw new RuntimeException('Erro ao gerar etiqueta no Melhor Envio.');
        }

        $generated    = $generateResponse->json($orderId) ?? [];
        $trackingCode = $generated['tracking'] ?? '';

        // 4. Obtém URL de impressão
        $printResponse = $this->http()->post("{$this->baseUrl}/me/shipment/print", [
            'orders' => [$orderId],
            'mode'   => 'public',
        ]);

        $labelUrl = $printResponse->successful() ? ($printResponse->json('url') ?? '') : '';

        return [
            'tracking_code' => $trackingCode,
            'label_url'     => $labelUrl,
        ];
    }

    /**
     * Consulta status de rastreio via API do Melhor Envio.
     */
    public function getTrackingStatus(string $trackingCode): string
    {
        $response = $this->http()
            ->get("{$this->baseUrl}/me/shipment/tracking", ['q' => $trackingCode]);

        if ($response->failed()) {
            return 'unknown';
        }

        $data   = collect($response->json())->first();
        $events = $data['tracking']['events'] ?? [];

        return ! empty($events) ? ($events[0]['type'] ?? 'unknown') : 'pending';
    }

    // ── Helpers privados ────────────────────────────────────────────────────

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withToken($this->token)->withHeaders([
            'User-Agent' => "LucraVendas ({$this->fromEmail})",
            'Accept'     => 'application/json',
        ]);
    }

    /**
     * Consolida múltiplos pacotes num único pacote para a API.
     * Soma o peso total e usa as maiores dimensões.
     */
    private function consolidatePackages(array $packages): array
    {
        if (empty($packages)) {
            $default = config('shipping.default_package');
            return [
                'weight' => $default['weight_grams'] / 1000,
                'length' => $default['length_cm'],
                'width'  => $default['width_cm'],
                'height' => $default['height_cm'],
            ];
        }

        $totalWeightGrams = array_sum(array_column($packages, 'weight_grams'));
        $maxLength = max(array_column($packages, 'length_cm'));
        $maxWidth  = max(array_column($packages, 'width_cm'));
        $sumHeight = array_sum(array_column($packages, 'height_cm'));

        return [
            'weight' => max(0.1, round($totalWeightGrams / 1000, 3)),
            'length' => max(16, $maxLength),   // mínimo exigido pelos Correios
            'width'  => max(11, $maxWidth),
            'height' => max(2, $sumHeight),
        ];
    }

    /**
     * Formata endereço para o payload da API.
     * Quando $override é fornecido (remetente), usa os dados de config.
     */
    private function formatAddress(ShippingAddress $addr, array $override = []): array
    {
        if (! empty($override)) {
            return [
                'name'       => $override['name'],
                'phone'      => $override['phone'],
                'email'      => $override['email'],
                'document'   => $override['document'],
                'address'    => $override['address'],
                'number'     => $override['number'],
                'city'       => $override['city'],
                'state_abbr' => $override['state'],
                'postal_code'=> preg_replace('/\D/', '', $override['zipcode']),
                'country_id' => 'BR',
            ];
        }

        return [
            'name'        => $addr->recipientName,
            'address'     => $addr->address,
            'number'      => $addr->number,
            'complement'  => $addr->complement ?? '',
            'city'        => $addr->city,
            'state_abbr'  => $addr->state,
            'postal_code' => $addr->zipcodeDigitsOnly(),
            'country_id'  => 'BR',
        ];
    }
}
