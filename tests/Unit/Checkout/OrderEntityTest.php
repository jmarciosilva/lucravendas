<?php

declare(strict_types=1);

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Orders\Domain\Entities\Order;
use App\Modules\Orders\Domain\ValueObjects\OrderStatus;

$criarPedido = fn () => Order::create(
    tenantId: 'tenant-1',
    userId: 1,
    subtotal: Money::fromCentavos(10000),
    discount: Money::fromCentavos(0),
    shippingCost: Money::fromCentavos(0),
    couponId: null,
    paymentMethod: null,
    notes: null,
    items: [],
);

it('cria pedido com status pending por padrão', function () use ($criarPedido) {
    $order = $criarPedido();

    expect($order->status()->value())->toBe(OrderStatus::PENDING);
});

it('calcula total corretamente: subtotal - desconto + frete', function () {
    $order = Order::create(
        tenantId: 'tenant-1',
        userId: 1,
        subtotal: Money::fromCentavos(10000),
        discount: Money::fromCentavos(1000),
        shippingCost: Money::fromCentavos(500),
        couponId: null,
        paymentMethod: null,
        notes: null,
        items: [],
    );

    expect($order->total()->centavos())->toBe(9500); // 100 - 10 + 5
});

it('permite transição pending → confirmed', function () use ($criarPedido) {
    $order = $criarPedido();
    $order->confirm();

    expect($order->status()->value())->toBe(OrderStatus::CONFIRMED);
});

it('permite transição confirmed → processing → shipped → delivered', function () use ($criarPedido) {
    $order = $criarPedido();
    $order->confirm();
    $order->markAsProcessing();
    $order->markAsShipped();
    $order->markAsDelivered();

    expect($order->status()->value())->toBe(OrderStatus::DELIVERED);
});

it('permite cancelar a partir de pending', function () use ($criarPedido) {
    $order = $criarPedido();
    $order->cancel();

    expect($order->status()->value())->toBe(OrderStatus::CANCELLED);
});

it('lança exceção em transição inválida (delivered → cancelled)', function () use ($criarPedido) {
    $order = $criarPedido();
    $order->confirm();
    $order->markAsProcessing();
    $order->markAsShipped();
    $order->markAsDelivered();

    expect(fn () => $order->cancel())
        ->toThrow(RuntimeException::class, "Transição de status inválida: 'delivered' → 'cancelled'.");
});

it('dispara evento OrderCreated ao chamar recordCreatedEvent', function () use ($criarPedido) {
    $order = $criarPedido();
    $order->recordCreatedEvent();
    $events = $order->pullDomainEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(\App\Modules\Orders\Domain\Events\OrderCreated::class);
});

it('pullDomainEvents limpa os eventos após retornar', function () use ($criarPedido) {
    $order = $criarPedido();
    $order->recordCreatedEvent();
    $order->pullDomainEvents(); // consome

    expect($order->pullDomainEvents())->toBeEmpty();
});
