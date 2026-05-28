<?php

declare(strict_types=1);

namespace App\Modules\Payments\Infrastructure\Gateways;

use App\Modules\Payments\Domain\Contracts\PaymentGatewayInterface;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use RuntimeException;

/**
 * Gateway de pagamento via Mercado Pago.
 *
 * Valores monetários: o SDK do MP recebe em REAIS (float).
 * Internamente o sistema trabalha em centavos (int).
 * A conversão acontece nesta classe: centavos / 100.0 → reais.
 */
class MercadoPagoGateway implements PaymentGatewayInterface
{
    private PaymentClient $client;

    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(
            config('payments.mercadopago.access_token')
        );

        $this->client = new PaymentClient();
    }

    public function createPixPayment(int $orderId, int $amountCentavos, string $payerEmail): array
    {
        $response = $this->client->create([
            'transaction_amount' => $amountCentavos / 100.0,
            'payment_method_id'  => 'pix',
            'description'        => "Pedido #{$orderId} — LucraVendas",
            'payer'              => ['email' => $payerEmail],
        ]);

        $this->assertNoError($response);

        return [
            'external_id'     => (string) $response->id,
            'status'          => $this->normalizeStatus($response->status),
            'qr_code'         => $response->point_of_interaction->transaction_data->qr_code ?? null,
            'qr_code_base64'  => $response->point_of_interaction->transaction_data->qr_code_base64 ?? null,
        ];
    }

    public function createCardPayment(
        int    $orderId,
        int    $amountCentavos,
        string $cardToken,
        int    $installments,
        string $payerEmail,
    ): array {
        $response = $this->client->create([
            'transaction_amount' => $amountCentavos / 100.0,
            'token'              => $cardToken,
            'installments'       => $installments,
            'description'        => "Pedido #{$orderId} — LucraVendas",
            'payer'              => ['email' => $payerEmail],
        ]);

        $this->assertNoError($response);

        return [
            'external_id'  => (string) $response->id,
            'status'       => $this->normalizeStatus($response->status),
            'installments' => $installments,
        ];
    }

    public function createBoletoPayment(
        int    $orderId,
        int    $amountCentavos,
        string $payerEmail,
        string $payerCpf,
    ): array {
        $response = $this->client->create([
            'transaction_amount' => $amountCentavos / 100.0,
            'payment_method_id'  => 'bolbradesco',
            'description'        => "Pedido #{$orderId} — LucraVendas",
            'payer'              => [
                'email'         => $payerEmail,
                'identification' => [
                    'type'   => 'CPF',
                    'number' => preg_replace('/\D/', '', $payerCpf),
                ],
            ],
        ]);

        $this->assertNoError($response);

        return [
            'external_id' => (string) $response->id,
            'status'      => $this->normalizeStatus($response->status),
            'ticket_url'  => $response->transaction_details->external_resource_url ?? null,
        ];
    }

    public function getPaymentStatus(string $externalId): string
    {
        $response = $this->client->get((int) $externalId);

        return $this->normalizeStatus($response->status ?? 'unknown');
    }

    public function refund(string $externalId, ?int $amountCentavos = null): bool
    {
        $data = [];

        if ($amountCentavos !== null) {
            $data['amount'] = $amountCentavos / 100.0;
        }

        $response = $this->client->refund((int) $externalId, $data);

        return isset($response->id);
    }

    /**
     * Normaliza os status do Mercado Pago para os status internos da aplicação.
     * MP retorna: pending, approved, authorized, in_process, in_mediation,
     *             rejected, cancelled, refunded, charged_back
     */
    private function normalizeStatus(string $mpStatus): string
    {
        return match ($mpStatus) {
            'approved'                     => 'approved',
            'rejected'                     => 'rejected',
            'cancelled'                    => 'cancelled',
            'refunded', 'charged_back'     => 'refunded',
            default                        => 'pending',
        };
    }

    private function assertNoError(mixed $response): void
    {
        if (isset($response->error)) {
            throw new RuntimeException(
                "Mercado Pago: {$response->message} (status: {$response->status})"
            );
        }
    }
}
