<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object que representa um valor monetário em reais brasileiros.
 *
 * Internamente armazena o valor em centavos (inteiro) para eliminar
 * problemas de arredondamento de ponto flutuante em operações financeiras.
 * Ex.: R$ 49,90 é armazenado como 4990.
 */
final class Money
{
    /** @param int $centavos Valor em centavos (sempre positivo ou zero) */
    private function __construct(
        private readonly int $centavos,
    ) {
        if ($centavos < 0) {
            throw new InvalidArgumentException('O valor monetário não pode ser negativo.');
        }
    }

    /** Cria a partir de centavos (formato interno do banco de dados) */
    public static function fromCentavos(int $centavos): self
    {
        return new self($centavos);
    }

    /**
     * Cria a partir de reais com casas decimais.
     * Ex.: Money::fromReais(49.90) → 4990 centavos
     */
    public static function fromReais(float $reais): self
    {
        return new self((int) round($reais * 100));
    }

    /** Retorna o valor em centavos (para persistência no banco) */
    public function centavos(): int
    {
        return $this->centavos;
    }

    /** Retorna o valor em reais (float) para exibição */
    public function reais(): float
    {
        return $this->centavos / 100;
    }

    /**
     * Formata o valor como moeda brasileira.
     * Ex.: 4990 → "R$ 49,90"
     */
    public function formatted(): string
    {
        return 'R$ ' . number_format($this->reais(), 2, ',', '.');
    }

    public function isZero(): bool
    {
        return $this->centavos === 0;
    }

    /** Verifica se este valor é menor que outro (para validar compare_price) */
    public function isLessThan(self $other): bool
    {
        return $this->centavos < $other->centavos;
    }

    public function equals(self $other): bool
    {
        return $this->centavos === $other->centavos;
    }
}
