<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Repositories;

use App\Modules\Marketplace\Domain\Entities\Seller;
use App\Modules\Marketplace\Domain\Repositories\SellerRepositoryInterface;
use App\Modules\Marketplace\Domain\ValueObjects\SellerStatus;
use App\Modules\Marketplace\Infrastructure\Models\SellerModel;

final class EloquentSellerRepository implements SellerRepositoryInterface
{
    public function save(Seller $seller): Seller
    {
        $model = SellerModel::create([
            'name'            => $seller->name(),
            'slug'            => $seller->slug(),
            'tenant_id'       => $seller->tenantId(),
            'user_id'         => $seller->userId(),
            'commission_rate' => $seller->commissionRate(),
            'status'          => $seller->status()->value(),
            'bank_info'       => $seller->bankInfo() ?: null,
            'description'     => $seller->description(),
        ]);

        return $this->toDomain($model);
    }

    public function update(Seller $seller): void
    {
        SellerModel::where('id', $seller->id())->update([
            'status'          => $seller->status()->value(),
            'commission_rate' => $seller->commissionRate(),
            'bank_info'       => $seller->bankInfo() ?: null,
            'description'     => $seller->description(),
        ]);
    }

    public function findById(int $id): ?Seller
    {
        $model = SellerModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findBySlug(string $slug, string $tenantId): ?Seller
    {
        $model = SellerModel::where('slug', $slug)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByUserId(int $userId, string $tenantId): ?Seller
    {
        $model = SellerModel::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    /** @return Seller[] */
    public function findActiveByTenant(string $tenantId): array
    {
        return SellerModel::where('tenant_id', $tenantId)
            ->where('status', SellerStatus::ACTIVE)
            ->orderBy('name')
            ->get()
            ->map(fn (SellerModel $m) => $this->toDomain($m))
            ->all();
    }

    public function slugExistsInTenant(string $slug, string $tenantId): bool
    {
        return SellerModel::where('slug', $slug)
            ->where('tenant_id', $tenantId)
            ->exists();
    }

    private function toDomain(SellerModel $model): Seller
    {
        return Seller::restore(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            tenantId: $model->tenant_id,
            userId: $model->user_id,
            commissionRate: (float) $model->commission_rate,
            status: SellerStatus::from($model->status),
            bankInfo: $model->bank_info ?? [],
            description: $model->description,
        );
    }
}
