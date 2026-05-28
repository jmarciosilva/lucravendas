<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Domain\Entities;

final class ShippingZone
{
    private function __construct(
        private readonly ?int   $id,
        private readonly string $tenantId,
        private readonly string $name,
        private readonly array  $states,   // ['SP', 'RJ', 'MG', ...]
        private readonly bool   $isActive,
    ) {}

    public static function create(
        string $tenantId,
        string $name,
        array  $states,
    ): self {
        return new self(
            id: null,
            tenantId: $tenantId,
            name: $name,
            states: array_map('strtoupper', $states),
            isActive: true,
        );
    }

    public static function restore(
        int    $id,
        string $tenantId,
        string $name,
        array  $states,
        bool   $isActive,
    ): self {
        return new self($id, $tenantId, $name, $states, $isActive);
    }

    /** Verifica se uma UF está coberta por esta zona */
    public function coversState(string $state): bool
    {
        return in_array(strtoupper($state), $this->states, true);
    }

    public function id(): ?int      { return $this->id; }
    public function tenantId(): string { return $this->tenantId; }
    public function name(): string  { return $this->name; }
    public function states(): array { return $this->states; }
    public function isActive(): bool { return $this->isActive; }
}
