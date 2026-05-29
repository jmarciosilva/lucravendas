<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Domain\ValueObjects;

final class PostStatus
{
    public const PENDING   = 'pending';
    public const PUBLISHED = 'published';
    public const FAILED    = 'failed';
    public const CANCELLED = 'cancelled';

    private const VALID = [self::PENDING, self::PUBLISHED, self::FAILED, self::CANCELLED];

    private function __construct(
        private readonly string $value,
    ) {}

    public static function fromString(string $value): self
    {
        if (! in_array($value, self::VALID, true)) {
            throw new \InvalidArgumentException("Status inválido: {$value}");
        }

        return new self($value);
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isPublished(): bool
    {
        return $this->value === self::PUBLISHED;
    }

    public function isFailed(): bool
    {
        return $this->value === self::FAILED;
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }
}
