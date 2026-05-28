<?php

declare(strict_types=1);

use App\Modules\Catalog\Domain\Entities\Product;
use App\Modules\Catalog\Domain\Events\ProductCreated;
use App\Modules\Catalog\Domain\Events\ProductUpdated;
use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Catalog\Domain\ValueObjects\ProductStatus;

describe('Entidade Product', function (): void {

    /** Cria um produto válido para uso nos testes */
    $criarProduto = fn () => Product::create(
        id: 1,
        name: 'Camiseta Básica',
        slug: 'camiseta-basica',
        price: Money::fromCentavos(4990),
        tenantId: 'tenant-abc',
    );

    it('deve criar produto com status draft por padrão', function () use ($criarProduto): void {
        $product = $criarProduto();

        expect($product->status()->isDraft())->toBeTrue()
            ->and($product->isActive())->toBeFalse();
    });

    it('deve registrar evento ProductCreated ao criar', function () use ($criarProduto): void {
        $product = $criarProduto();
        $events  = $product->pullDomainEvents();

        expect($events)->toHaveCount(1)
            ->and($events[0])->toBeInstanceOf(ProductCreated::class);
    });

    it('deve limpar eventos após pullDomainEvents', function () use ($criarProduto): void {
        $product = $criarProduto();
        $product->pullDomainEvents();

        expect($product->pullDomainEvents())->toBeEmpty();
    });

    it('deve publicar o produto alterando status para active', function () use ($criarProduto): void {
        $product = $criarProduto();
        $product->pullDomainEvents();

        $product->publish();

        expect($product->isActive())->toBeTrue()
            ->and($product->pullDomainEvents()[0])->toBeInstanceOf(ProductUpdated::class);
    });

    it('deve decrementar estoque corretamente', function () use ($criarProduto): void {
        $product = Product::create(
            id: 1,
            name: 'Produto',
            slug: 'produto',
            price: Money::fromCentavos(1000),
            tenantId: 'tenant-abc',
            stock: 10,
        );
        $product->pullDomainEvents();

        $product->decrementStock(3);

        expect($product->stock())->toBe(7);
    });

    it('deve lançar exceção ao decrementar mais do que o estoque disponível', function () use ($criarProduto): void {
        $product = $criarProduto();

        $product->decrementStock(1);
    })->throws(\InvalidArgumentException::class, 'Estoque insuficiente');

    it('deve identificar produto com estoque baixo', function (): void {
        $product = Product::create(
            id: 1,
            name: 'Produto',
            slug: 'produto',
            price: Money::fromCentavos(1000),
            tenantId: 'tenant-abc',
            stock: 3,
        );

        expect($product->isLowStock())->toBeTrue()
            ->and($product->isLowStock(3))->toBeFalse();
    });

    it('deve lançar exceção ao criar produto com preço de comparação menor que o preço', function (): void {
        Product::create(
            id: 1,
            name: 'Produto',
            slug: 'produto',
            price: Money::fromCentavos(5000),
            tenantId: 'tenant-abc',
            comparePrice: Money::fromCentavos(3000),
        );
    })->throws(\InvalidArgumentException::class, 'comparação');

    it('deve lançar exceção para slug com formato inválido', function (): void {
        Product::create(
            id: 1,
            name: 'Produto',
            slug: 'Slug Inválido!',
            price: Money::fromCentavos(1000),
            tenantId: 'tenant-abc',
        );
    })->throws(\InvalidArgumentException::class, 'Slug inválido');

});
