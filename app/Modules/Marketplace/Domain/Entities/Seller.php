<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Entities;

use App\Modules\Marketplace\Domain\ValueObjects\SellerStatus;
use RuntimeException;

final class Seller
{
    private function __construct(
        private readonly ?int    $id,
        private readonly string  $name,
        private readonly string  $slug,
        private readonly string  $tenantId,
        private readonly ?int    $userId,
        private readonly float   $commissionRate,
        private SellerStatus     $status,
        private readonly array   $bankInfo,
        private readonly ?string $description,
    ) {}

    public static function create(
        string  $name,
        string  $slug,
        string  $tenantId,
        ?int    $userId,
        float   $commissionRate,
        array   $bankInfo = [],
        ?string $description = null,
    ): self {
        if ($commissionRate < 0 || $commissionRate > 100) {
            throw new RuntimeException('Taxa de comissão deve estar entre 0 e 100.');
        }

        return new self(
            id: null,
            name: $name,
            slug: $slug,
            tenantId: $tenantId,
            userId: $userId,
            commissionRate: $commissionRate,
            status: SellerStatus::from(SellerStatus::PENDING),
            bankInfo: $bankInfo,
            description: $description,
        );
    }

    public static function restore(
        int          $id,
        string       $name,
        string       $slug,
        string       $tenantId,
        ?int         $userId,
        float        $commissionRate,
        SellerStatus $status,
        array        $bankInfo,
        ?string      $description,
    ): self {
        return new self($id, $name, $slug, $tenantId, $userId, $commissionRate, $status, $bankInfo, $description);
    }

    /** Aprova o seller para operar no marketplace */
    public function approve(): void
    {
        $this->status = $this->status->approve();
    }

    /** Suspende o seller — produtos não aparecem até reativação */
    public function suspend(): void
    {
        $this->status = $this->status->suspend();
    }

    /** Retorna a taxa decimal para cálculo (ex: 10.0% → 0.10) */
    public function commissionRateDecimal(): float
    {
        return $this->commissionRate / 100.0;
    }

    public function id(): ?int           { return $this->id; }
    public function name(): string       { return $this->name; }
    public function slug(): string       { return $this->slug; }
    public function tenantId(): string   { return $this->tenantId; }
    public function userId(): ?int       { return $this->userId; }
    public function commissionRate(): float { return $this->commissionRate; }
    public function status(): SellerStatus  { return $this->status; }
    public function bankInfo(): array    { return $this->bankInfo; }
    public function description(): ?string { return $this->description; }
}
