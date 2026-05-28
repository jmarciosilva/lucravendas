<?php

declare(strict_types=1);

use App\Modules\Catalog\Domain\ValueObjects\Money;

describe('Value Object Money', function (): void {

    it('deve criar a partir de centavos e retornar o valor correto em reais', function (): void {
        $money = Money::fromCentavos(4990);

        expect($money->centavos())->toBe(4990)
            ->and($money->reais())->toBe(49.90);
    });

    it('deve criar a partir de reais e converter corretamente para centavos', function (): void {
        $money = Money::fromReais(49.90);

        expect($money->centavos())->toBe(4990);
    });

    it('deve formatar o valor como moeda brasileira', function (): void {
        $money = Money::fromCentavos(4990);

        expect($money->formatted())->toBe('R$ 49,90');
    });

    it('deve identificar valor zero corretamente', function (): void {
        expect(Money::fromCentavos(0)->isZero())->toBeTrue()
            ->and(Money::fromCentavos(1)->isZero())->toBeFalse();
    });

    it('deve comparar dois valores monetários corretamente', function (): void {
        $menor = Money::fromCentavos(1000);
        $maior = Money::fromCentavos(2000);

        expect($menor->isLessThan($maior))->toBeTrue()
            ->and($maior->isLessThan($menor))->toBeFalse();
    });

    it('deve lançar exceção para valor negativo', function (): void {
        Money::fromCentavos(-1);
    })->throws(\InvalidArgumentException::class, 'negativo');

});
