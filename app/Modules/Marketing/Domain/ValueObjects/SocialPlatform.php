<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Domain\ValueObjects;

final class SocialPlatform
{
    public const INSTAGRAM = 'instagram';
    public const FACEBOOK  = 'facebook';

    private const VALID = [self::INSTAGRAM, self::FACEBOOK];

    private function __construct(
        private readonly string $value,
    ) {}

    public static function fromString(string $value): self
    {
        if (! in_array($value, self::VALID, true)) {
            throw new \InvalidArgumentException("Plataforma inválida: {$value}. Use: " . implode(', ', self::VALID));
        }

        return new self($value);
    }

    public static function instagram(): self
    {
        return new self(self::INSTAGRAM);
    }

    public static function facebook(): self
    {
        return new self(self::FACEBOOK);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isInstagram(): bool
    {
        return $this->value === self::INSTAGRAM;
    }

    public function isFacebook(): bool
    {
        return $this->value === self::FACEBOOK;
    }

    public static function valid(): array
    {
        return self::VALID;
    }
}
