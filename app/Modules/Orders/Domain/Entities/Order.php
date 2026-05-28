<?php

declare(strict_types=1);

namespace App\Modules\Orders\Domain\Entities;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Orders\Domain\Events\OrderCreated;
use App\Modules\Orders\Domain\ValueObjects\OrderStatus;
use App\Modules\Orders\Domain\ValueObjects\PaymentStatus;

final class Order
{
    private array $domainEvents = [];

    /** @param OrderItem[] $items */
    private function __construct(
        private readonly ?int         $id,
        private readonly string       $tenantId,
        private readonly int          $userId,
        private OrderStatus           $status,
        private readonly Money        $subtotal,
        private readonly Money        $discount,
        private readonly Money        $shippingCost,
        private readonly Money        $total,
        private PaymentStatus         $paymentStatus,
        private readonly ?int         $couponId,
        private readonly ?string      $paymentMethod,
        private readonly ?string      $notes,
        private readonly array        $items,
    ) {}

    public static function create(
        string  $tenantId,
        int     $userId,
        Money   $subtotal,
        Money   $discount,
        Money   $shippingCost,
        ?int    $couponId,
        ?string $paymentMethod,
        ?string $notes,
        array   $items,
    ): self {
        $total = Money::fromCentavos(
            $subtotal->centavos() - $discount->centavos() + $shippingCost->centavos()
        );

        $order = new self(
            id: null,
            tenantId: $tenantId,
            userId: $userId,
            status: OrderStatus::from(OrderStatus::PENDING),
            subtotal: $subtotal,
            discount: $discount,
            shippingCost: $shippingCost,
            total: $total,
            paymentStatus: PaymentStatus::from(PaymentStatus::PENDING),
            couponId: $couponId,
            paymentMethod: $paymentMethod,
            notes: $notes,
            items: $items,
        );

        return $order;
    }

    /** @param OrderItem[] $items */
    public static function restore(
        int           $id,
        string        $tenantId,
        int           $userId,
        OrderStatus   $status,
        Money         $subtotal,
        Money         $discount,
        Money         $shippingCost,
        Money         $total,
        PaymentStatus $paymentStatus,
        ?int          $couponId,
        ?string       $paymentMethod,
        ?string       $notes,
        array         $items,
    ): self {
        return new self(
            $id, $tenantId, $userId, $status, $subtotal, $discount,
            $shippingCost, $total, $paymentStatus, $couponId, $paymentMethod, $notes, $items
        );
    }

    public function recordCreatedEvent(): void
    {
        $this->domainEvents[] = new OrderCreated(
            orderId: $this->id ?? 0,
            userId: $this->userId,
            tenantId: $this->tenantId,
            total: $this->total->centavos(),
        );
    }

    public function confirm(): void
    {
        $this->status = $this->status->transitionTo(OrderStatus::CONFIRMED);
    }

    public function cancel(): void
    {
        $this->status = $this->status->transitionTo(OrderStatus::CANCELLED);
    }

    public function markAsProcessing(): void
    {
        $this->status = $this->status->transitionTo(OrderStatus::PROCESSING);
    }

    public function markAsShipped(): void
    {
        $this->status = $this->status->transitionTo(OrderStatus::SHIPPED);
    }

    public function markAsDelivered(): void
    {
        $this->status = $this->status->transitionTo(OrderStatus::DELIVERED);
    }

    /**
     * Marca o pagamento como aprovado e confirma o pedido.
     * Chamado pelo ProcessWebhookHandler ao receber status 'approved' do MP.
     */
    public function markAsPaid(): void
    {
        $this->paymentStatus = PaymentStatus::from(PaymentStatus::PAID);
        $this->status        = $this->status->transitionTo(OrderStatus::CONFIRMED);
    }

    /**
     * Marca pagamento como falho e cancela o pedido.
     * Chamado pelo ProcessWebhookHandler ao receber 'rejected' ou 'cancelled'.
     */
    public function markAsPaymentFailed(): void
    {
        $this->paymentStatus = PaymentStatus::from(PaymentStatus::FAILED);
        $this->status        = $this->status->transitionTo(OrderStatus::CANCELLED);
    }

    /**
     * Marca pagamento como estornado.
     * Chamado pelo RefundPaymentHandler após confirmar estorno no MP.
     */
    public function markAsRefunded(): void
    {
        $this->paymentStatus = PaymentStatus::from(PaymentStatus::REFUNDED);
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    public function id(): ?int              { return $this->id; }
    public function tenantId(): string      { return $this->tenantId; }
    public function userId(): int           { return $this->userId; }
    public function status(): OrderStatus   { return $this->status; }
    public function subtotal(): Money       { return $this->subtotal; }
    public function discount(): Money       { return $this->discount; }
    public function shippingCost(): Money   { return $this->shippingCost; }
    public function total(): Money          { return $this->total; }
    public function paymentStatus(): PaymentStatus { return $this->paymentStatus; }
    public function couponId(): ?int        { return $this->couponId; }
    public function paymentMethod(): ?string { return $this->paymentMethod; }
    public function notes(): ?string        { return $this->notes; }

    /** @return OrderItem[] */
    public function items(): array          { return $this->items; }
}
