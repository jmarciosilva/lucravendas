<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Application\UseCases\CalculateShipping;

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Orders\Application\UseCases\GetCart\GetCartHandler;
use App\Modules\Shipping\Application\Services\InternalRateCalculator;
use App\Modules\Shipping\Domain\Contracts\ShippingGatewayInterface;
use App\Modules\Shipping\Domain\ValueObjects\ShippingOption;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Calcula as opções de frete disponíveis para o carrinho atual.
 *
 * Mescla resultados do sistema interno (zonas/tarifas) e do gateway externo (ME),
 * conforme a configuração 'shipping.gateway' do tenant.
 */
final class CalculateShippingHandler
{
    public function __construct(
        private readonly GetCartHandler          $getCartHandler,
        private readonly InternalRateCalculator  $internalCalculator,
        private readonly ShippingGatewayInterface $gateway,
    ) {}

    /**
     * @return ShippingOption[]
     */
    public function handle(CalculateShippingCommand $command): array
    {
        // Obtém o carrinho para extrair itens e calcular peso
        $cart = $this->getCartHandler->handle(
            $command->tenantId,
            $command->sessionId,
            $command->userId,
        );

        if ($cart->isEmpty()) {
            throw new RuntimeException('Carrinho vazio — não é possível calcular frete.');
        }

        [$totalWeightGrams, $packages] = $this->buildPackageData($cart->items());

        // Origem: zipcode do tenant (via config global ou fallback)
        $fromZipcode = $this->getTenantOriginZipcode($command->tenantId);

        $options    = [];
        $gatewayMode = config('shipping.gateway', 'both');

        // Usa o subtotal real do carrinho para verificar threshold de frete grátis
        $subtotalCentavos = $cart->subtotal()->centavos();

        // Tabela interna
        if (in_array($gatewayMode, ['internal', 'both'], true)) {
            $internalOptions = $this->internalCalculator->calculate(
                state: $command->toState,
                tenantId: $command->tenantId,
                totalWeightGrams: $totalWeightGrams,
                subtotalCentavos: $subtotalCentavos,
            );

            $options = array_merge($options, $internalOptions);
        }

        // Gateway externo (Melhor Envio)
        if (in_array($gatewayMode, ['melhorenvio', 'both'], true) && ! empty(config('shipping.melhorenvio.token'))) {
            try {
                $meOptions = $this->gateway->calculateRates(
                    fromZipcode: $fromZipcode,
                    toZipcode: $command->toZipcode,
                    packages: $packages,
                    insuranceValueCentavos: (int) config('shipping.melhorenvio.insurance_value_centavos', 0),
                );

                $options = array_merge($options, $meOptions);
            } catch (\Throwable) {
                // Gateway indisponível: retorna apenas opções internas sem lançar exceção
            }
        }

        // Ordena pelo preço (frete grátis primeiro, depois crescente)
        usort($options, fn (ShippingOption $a, ShippingOption $b) => $a->priceCentavos <=> $b->priceCentavos);

        return $options;
    }

    /**
     * Extrai peso total e dados de cada pacote a partir dos itens do carrinho.
     *
     * @param array $items
     * @return array{0: int, 1: array}  [totalWeightGrams, packages[]]
     */
    private function buildPackageData(array $items): array
    {
        $default      = config('shipping.default_package');
        $totalWeight  = 0;
        $packages     = [];

        foreach ($items as $item) {
            $product    = ProductModel::find($item->productId());
            $qty        = $item->quantity();
            $weightUnit = $product?->weight_grams ?? $default['weight_grams'];

            $totalWeight += $weightUnit * $qty;

            for ($i = 0; $i < $qty; $i++) {
                $packages[] = [
                    'weight_grams' => $weightUnit,
                    'length_cm'    => $product?->length_cm ?? $default['length_cm'],
                    'width_cm'     => $product?->width_cm  ?? $default['width_cm'],
                    'height_cm'    => $product?->height_cm ?? $default['height_cm'],
                ];
            }
        }

        return [$totalWeight ?: $default['weight_grams'], $packages];
    }

    private function getTenantOriginZipcode(string $tenantId): string
    {
        $zipcode = DB::table('tenants')->where('id', $tenantId)->value('origin_zipcode');

        return $zipcode ?? config('shipping.melhorenvio.from.zipcode', '01310-100');
    }
}
