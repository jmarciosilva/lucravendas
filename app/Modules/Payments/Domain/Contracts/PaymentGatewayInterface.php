<?php

declare(strict_types=1);

namespace App\Modules\Payments\Domain\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Gera cobrança PIX e retorna os dados para exibição do QR Code.
     * Retorna array com: external_id, qr_code, qr_code_base64, status
     */
    public function createPixPayment(int $orderId, int $amountCentavos, string $payerEmail): array;

    /**
     * Processa pagamento com cartão de crédito.
     * O card_token é gerado pelo frontend via SDK JS do Mercado Pago.
     * Retorna array com: external_id, status, installments
     */
    public function createCardPayment(
        int    $orderId,
        int    $amountCentavos,
        string $cardToken,
        int    $installments,
        string $payerEmail,
    ): array;

    /**
     * Gera boleto bancário para pagamento.
     * Retorna array com: external_id, ticket_url, status
     */
    public function createBoletoPayment(
        int    $orderId,
        int    $amountCentavos,
        string $payerEmail,
        string $payerCpf,
    ): array;

    /**
     * Consulta o status atual de um pagamento pelo ID externo do MP.
     * Retorna: 'pending' | 'approved' | 'rejected' | 'cancelled' | 'refunded'
     */
    public function getPaymentStatus(string $externalId): string;

    /**
     * Estorna um pagamento aprovado.
     * Se amountCentavos for null, estorna o valor total.
     */
    public function refund(string $externalId, ?int $amountCentavos = null): bool;
}
