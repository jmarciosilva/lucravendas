<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Entities;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Marketplace\Domain\ValueObjects\CommissionStatus;

final class Commission
{
    private function __construct(
        private readonly ?int          $id,
        private readonly int           $orderItemId,
        private readonly int           $sellerId,
        private readonly Money         $grossAmount,
        private readonly Money         $commissionAmount,
        private readonly Money         $netAmount,
        private CommissionStatus       $status,
    ) {}

    /**
     * Calcula e cria uma comissão para um item de pedido.
     *
     * @param float $commissionRate Taxa percentual (ex: 10.0 para 10%)
     */
    public static function calculate(
        int   $orderItemId,
        int   $sellerId,
        Money $grossAmount,
        float $commissionRate,
    ): self {
        $commissionCentavos = (int) round($grossAmount->centavos() * ($commissionRate / 100.0));
        $netCentavos        = $grossAmount->centavos() - $commissionCentavos;

        return new self(
            id: null,
            orderItemId: $orderItemId,
            sellerId: $sellerId,
            grossAmount: $grossAmount,
            commissionAmount: Money::fromCentavos($commissionCentavos),
            netAmount: Money::fromCentavos($netCentavos),
            status: CommissionStatus::from(CommissionStatus::PENDING),
        );
    }

    public static function restore(
        int              $id,
        int              $orderItemId,
        int              $sellerId,
        Money            $grossAmount,
        Money            $commissionAmount,
        Money            $netAmount,
        CommissionStatus $status,
    ): self {
        return new self($id, $orderItemId, $sellerId, $grossAmount, $commissionAmount, $netAmount, $status);
    }

    /** Marca comissão como paga após repasse */
    public function markAsPaid(): void
    {
        $this->status = CommissionStatus::from(CommissionStatus::PAID);
    }

    /** Cancela comissão quando o pedido é cancelado/recusado */
    public function cancel(): void
    {
        $this->status = CommissionStatus::from(CommissionStatus::CANCELLED);
    }

    public function id(): ?int                   { return $this->id; }
    public function orderItemId(): int           { return $this->orderItemId; }
    public function sellerId(): int              { return $this->sellerId; }
    public function grossAmount(): Money         { return $this->grossAmount; }
    public function commissionAmount(): Money    { return $this->commissionAmount; }
    public function netAmount(): Money           { return $this->netAmount; }
    public function status(): CommissionStatus   { return $this->status; }
}
