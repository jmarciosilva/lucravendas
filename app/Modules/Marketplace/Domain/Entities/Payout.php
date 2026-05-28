<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Entities;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Marketplace\Domain\ValueObjects\PayoutStatus;

final class Payout
{
    private function __construct(
        private readonly ?int   $id,
        private readonly int    $sellerId,
        private readonly Money  $amount,
        private PayoutStatus    $status,
        private ?string         $paidAt,
        private ?array          $gatewayResponse,
    ) {}

    public static function create(int $sellerId, Money $amount): self
    {
        return new self(
            id: null,
            sellerId: $sellerId,
            amount: $amount,
            status: PayoutStatus::from(PayoutStatus::PENDING),
            paidAt: null,
            gatewayResponse: null,
        );
    }

    public static function restore(
        int          $id,
        int          $sellerId,
        Money        $amount,
        PayoutStatus $status,
        ?string      $paidAt,
        ?array       $gatewayResponse,
    ): self {
        return new self($id, $sellerId, $amount, $status, $paidAt, $gatewayResponse);
    }

    /** Registra repasse como efetivado */
    public function markAsPaid(array $gatewayResponse, string $paidAt): void
    {
        $this->status          = PayoutStatus::from(PayoutStatus::PAID);
        $this->gatewayResponse = $gatewayResponse;
        $this->paidAt          = $paidAt;
    }

    /** Registra falha no repasse */
    public function markAsFailed(array $gatewayResponse): void
    {
        $this->status          = PayoutStatus::from(PayoutStatus::FAILED);
        $this->gatewayResponse = $gatewayResponse;
    }

    public function id(): ?int              { return $this->id; }
    public function sellerId(): int         { return $this->sellerId; }
    public function amount(): Money         { return $this->amount; }
    public function status(): PayoutStatus  { return $this->status; }
    public function paidAt(): ?string       { return $this->paidAt; }
    public function gatewayResponse(): ?array { return $this->gatewayResponse; }
}
