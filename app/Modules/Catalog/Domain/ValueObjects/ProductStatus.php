<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object que representa o status de publicação de um produto.
 *
 * - draft: rascunho, visível apenas para o admin do tenant
 * - active: publicado, visível no catálogo público
 * - inactive: desativado manualmente pelo lojista
 */
final class ProductStatus
{
    public const DRAFT    = 'draft';
    public const ACTIVE   = 'active';
    public const INACTIVE = 'inactive';

    /** @var list<string> Status válidos aceitos pelo sistema */
    private const VALID = [self::DRAFT, self::ACTIVE, self::INACTIVE];

    private function __construct(
        private readonly string $value,
    ) {}

    public static function fromString(string $value): self
    {
        if (! in_array($value, self::VALID, true)) {
            throw new InvalidArgumentException(
                "Status de produto inválido: '{$value}'. Valores aceitos: " . implode(', ', self::VALID)
            );
        }

        return new self($value);
    }

    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    /** @return list<string> */
    public static function valid(): array
    {
        return self::VALID;
    }
}
