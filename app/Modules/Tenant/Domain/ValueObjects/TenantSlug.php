<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Slug único que identifica o tenant em URLs e subdomínios.
 *
 * Imutável por design: o slug de um tenant nunca deve mudar após a criação,
 * pois é usado em subdomínios DNS e links externos.
 */
final class TenantSlug
{
    private const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    private function __construct(
        private readonly string $value,
    ) {
        $this->validate($value);
    }

    public static function fromString(string $slug): self
    {
        return new self(strtolower(trim($slug)));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private function validate(string $slug): void
    {
        if (strlen($slug) < 3 || strlen($slug) > 63) {
            throw new InvalidArgumentException('Slug deve ter entre 3 e 63 caracteres.');
        }

        if (! preg_match(self::PATTERN, $slug)) {
            throw new InvalidArgumentException(
                'Slug inválido. Use apenas letras minúsculas, números e hífens.'
            );
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
