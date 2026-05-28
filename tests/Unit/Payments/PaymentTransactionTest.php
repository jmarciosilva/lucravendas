<?php

declare(strict_types=1);

use App\Modules\Orders\Domain\Entities\Order;
use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Orders\Domain\ValueObjects\OrderStatus;
use App\Modules\Orders\Domain\ValueObjects\PaymentStatus;

it('markAsPaid define payment_status=paid e status=confirmed', function () {
    $order = Order::restore(
        id: 1,
        tenantId: 'tenant-1',
        userId: 1,
        status: OrderStatus::from(OrderStatus::PENDING),
        subtotal: Money::fromCentavos(10000),
        discount: Money::fromCentavos(0),
        shippingCost: Money::fromCentavos(0),
        total: Money::fromCentavos(10000),
        paymentStatus: PaymentStatus::from(PaymentStatus::PENDING),
        couponId: null,
        paymentMethod: 'pix',
        notes: null,
        items: [],
    );

    $order->markAsPaid();

    expect($order->paymentStatus()->value())->toBe(PaymentStatus::PAID)
        ->and($order->status()->value())->toBe(OrderStatus::CONFIRMED);
});

it('markAsPaymentFailed define payment_status=failed e status=cancelled', function () {
    $order = Order::restore(
        id: 1,
        tenantId: 'tenant-1',
        userId: 1,
        status: OrderStatus::from(OrderStatus::PENDING),
        subtotal: Money::fromCentavos(5000),
        discount: Money::fromCentavos(0),
        shippingCost: Money::fromCentavos(0),
        total: Money::fromCentavos(5000),
        paymentStatus: PaymentStatus::from(PaymentStatus::PENDING),
        couponId: null,
        paymentMethod: 'credit_card',
        notes: null,
        items: [],
    );

    $order->markAsPaymentFailed();

    expect($order->paymentStatus()->value())->toBe(PaymentStatus::FAILED)
        ->and($order->status()->value())->toBe(OrderStatus::CANCELLED);
});

it('markAsRefunded define payment_status=refunded', function () {
    $order = Order::restore(
        id: 1,
        tenantId: 'tenant-1',
        userId: 1,
        status: OrderStatus::from(OrderStatus::CONFIRMED),
        subtotal: Money::fromCentavos(10000),
        discount: Money::fromCentavos(0),
        shippingCost: Money::fromCentavos(0),
        total: Money::fromCentavos(10000),
        paymentStatus: PaymentStatus::from(PaymentStatus::PAID),
        couponId: null,
        paymentMethod: 'pix',
        notes: null,
        items: [],
    );

    $order->markAsRefunded();

    expect($order->paymentStatus()->value())->toBe(PaymentStatus::REFUNDED);
});

it('normalização dos status do Mercado Pago', function () {
    $normalizer = function (string $mpStatus): string {
        return match ($mpStatus) {
            'approved'                 => 'approved',
            'rejected'                 => 'rejected',
            'cancelled'                => 'cancelled',
            'refunded', 'charged_back' => 'refunded',
            default                    => 'pending',
        };
    };

    expect($normalizer('approved'))->toBe('approved')
        ->and($normalizer('rejected'))->toBe('rejected')
        ->and($normalizer('in_process'))->toBe('pending')
        ->and($normalizer('charged_back'))->toBe('refunded')
        ->and($normalizer('pending'))->toBe('pending');
});
