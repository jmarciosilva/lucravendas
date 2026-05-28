<?php

declare(strict_types=1);

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Orders\Domain\Entities\Cart;
use App\Modules\Orders\Domain\Entities\CartItem;

$criarItem = fn (int $productId, int $qty, int $priceCentavos, ?int $variantId = null) => CartItem::restore(
    id: $productId,
    cartId: 1,
    productId: $productId,
    variantId: $variantId,
    quantity: $qty,
    unitPrice: Money::fromCentavos($priceCentavos),
    productName: "Produto {$productId}",
);

it('começa com subtotal zero quando sem itens', function () {
    $cart = Cart::create('tenant-1', 'session-abc', null);

    expect($cart->subtotal()->centavos())->toBe(0)
        ->and($cart->isEmpty())->toBeTrue();
});

it('calcula o subtotal corretamente', function () use ($criarItem) {
    $cart = Cart::create('tenant-1', 'session-abc', null);
    $cart->addItem($criarItem(1, 2, 5000)); // R$ 50,00 × 2 = R$ 100,00
    $cart->addItem($criarItem(2, 1, 3000)); // R$ 30,00 × 1 = R$ 30,00

    expect($cart->subtotal()->centavos())->toBe(13000); // R$ 130,00
});

it('faz merge de itens do mesmo produto ao adicionar novamente', function () use ($criarItem) {
    $cart = Cart::create('tenant-1', 'session-abc', null);
    $cart->addItem($criarItem(1, 2, 5000));
    $cart->addItem($criarItem(1, 3, 5000)); // mesmo produto

    expect(count($cart->items()))->toBe(1)
        ->and($cart->items()[0]->quantity())->toBe(5);
});

it('calcula desconto percentual corretamente', function () use ($criarItem) {
    $cart = Cart::create('tenant-1', 'session-abc', null);
    $cart->addItem($criarItem(1, 1, 10000)); // R$ 100,00

    $discount = $cart->calculateDiscount('percent', 10); // 10%

    expect($discount->centavos())->toBe(1000); // R$ 10,00
});

it('calcula desconto fixo corretamente', function () use ($criarItem) {
    $cart = Cart::create('tenant-1', 'session-abc', null);
    $cart->addItem($criarItem(1, 1, 10000)); // R$ 100,00

    $discount = $cart->calculateDiscount('fixed', 2000); // R$ 20,00 fixo

    expect($discount->centavos())->toBe(2000);
});

it('desconto fixo não supera o subtotal', function () use ($criarItem) {
    $cart = Cart::create('tenant-1', 'session-abc', null);
    $cart->addItem($criarItem(1, 1, 5000)); // R$ 50,00

    $discount = $cart->calculateDiscount('fixed', 9999); // maior que o subtotal

    expect($discount->centavos())->toBe(5000); // limitado ao subtotal
});

it('remove um item pelo id', function () use ($criarItem) {
    $cart = Cart::create('tenant-1', 'session-abc', null);
    $cart->addItem($criarItem(1, 1, 5000));
    $cart->addItem($criarItem(2, 1, 3000));

    $cart->removeItem(1);

    expect(count($cart->items()))->toBe(1)
        ->and($cart->items()[0]->productId())->toBe(2);
});

it('lança exceção se criar carrinho sem session_id nem user_id', function () {
    expect(fn () => Cart::create('tenant-1', null, null))
        ->toThrow(RuntimeException::class);
});
