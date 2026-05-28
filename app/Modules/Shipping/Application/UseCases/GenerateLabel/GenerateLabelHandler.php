<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Application\UseCases\GenerateLabel;

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Orders\Infrastructure\Models\OrderItemModel;
use App\Modules\Shipping\Domain\Contracts\ShippingGatewayInterface;
use App\Modules\Shipping\Domain\ValueObjects\ShippingAddress;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Gera etiqueta de envio via gateway (Melhor Envio) após confirmação do pagamento.
 *
 * Acionado pelo listener do evento PaymentApproved.
 * Atualiza tracking_code e shipping_label_url no pedido.
 */
final class GenerateLabelHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface  $orderRepository,
        private readonly ShippingGatewayInterface  $gateway,
    ) {}

    public function handle(GenerateLabelCommand $command): void
    {
        $order = $this->orderRepository->findByIdRaw($command->orderId);

        if ($order === null) {
            throw new RuntimeException("Pedido #{$command->orderId} não encontrado.");
        }

        // Se já tem etiqueta gerada ou não tem endereço de entrega, não processa
        if ($order->trackingCode() !== null || $order->shippingAddress() === null) {
            return;
        }

        $shippingAddress = $order->shippingAddress();

        // Constrói o "from" com os dados de config do remetente
        $fromConfig = config('shipping.melhorenvio.from');
        if (empty($fromConfig['zipcode']) || empty($fromConfig['name'])) {
            Log::warning("GenerateLabelHandler: configuração do remetente incompleta para o pedido #{$command->orderId}.");
            return;
        }

        $fromAddress = new ShippingAddress(
            recipientName: $fromConfig['name'],
            zipcode: $fromConfig['zipcode'],
            address: $fromConfig['address'],
            number: $fromConfig['number'],
            complement: null,
            city: $fromConfig['city'],
            state: $fromConfig['state'],
        );

        // Monta pacotes a partir dos itens do pedido
        $default  = config('shipping.default_package');
        $packages = [];

        foreach (OrderItemModel::where('order_id', $command->orderId)->get() as $item) {
            $product = ProductModel::find($item->product_id);

            for ($i = 0; $i < $item->quantity; $i++) {
                $packages[] = [
                    'weight_grams' => $product?->weight_grams ?? $default['weight_grams'],
                    'length_cm'    => $product?->length_cm    ?? $default['length_cm'],
                    'width_cm'     => $product?->width_cm     ?? $default['width_cm'],
                    'height_cm'    => $product?->height_cm    ?? $default['height_cm'],
                ];
            }
        }

        try {
            $result = $this->gateway->generateLabel(
                from: $fromAddress,
                to: $shippingAddress,
                packages: $packages,
                serviceCode: $order->shippingServiceCode() ?? '2', // 2 = SEDEX como fallback
                insuranceValueCentavos: 0,
                externalOrderId: $command->orderId,
            );

            $this->orderRepository->updateTracking(
                orderId: $command->orderId,
                trackingCode: $result['tracking_code'],
                trackingStatus: 'pending',
                labelUrl: $result['label_url'],
            );

            Log::info("Etiqueta gerada para pedido #{$command->orderId}: {$result['tracking_code']}");
        } catch (\Throwable $e) {
            Log::error("Erro ao gerar etiqueta do pedido #{$command->orderId}: {$e->getMessage()}");
        }
    }
}
