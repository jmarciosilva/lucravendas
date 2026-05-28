<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Infrastructure\Repositories;

use App\Modules\Tenant\Domain\Entities\Tenant;
use App\Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;
use App\Modules\Tenant\Domain\ValueObjects\TenantId;
use App\Modules\Tenant\Domain\ValueObjects\TenantPlan;
use App\Modules\Tenant\Domain\ValueObjects\TenantSlug;
use App\Modules\Tenant\Infrastructure\Models\TenantModel;

/**
 * Implementação do repositório de Tenant usando Eloquent.
 *
 * Responsável por traduzir entre o modelo Eloquent e a entidade de domínio.
 * O domínio nunca importa este arquivo diretamente — sempre via interface.
 */
final class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function save(Tenant $tenant): void
    {
        TenantModel::updateOrCreate(
            ['id' => $tenant->id()->value()],
            [
                'name'   => $tenant->name(),
                'slug'   => $tenant->slug()->value(),
                'plan'   => $tenant->plan()->value(),
                'status' => $tenant->status(),
            ]
        );
    }

    public function findById(TenantId $id): ?Tenant
    {
        $model = TenantModel::find($id->value());

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(TenantSlug $slug): ?Tenant
    {
        $model = TenantModel::where('slug', $slug->value())->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function existsBySlug(TenantSlug $slug): bool
    {
        return TenantModel::where('slug', $slug->value())->exists();
    }

    /**
     * Reconstrói a entidade de domínio a partir do modelo Eloquent.
     * Centraliza o mapeamento num único lugar para facilitar manutenção.
     */
    private function toEntity(TenantModel $model): Tenant
    {
        return Tenant::create(
            id: TenantId::fromString($model->id),
            name: $model->name,
            slug: TenantSlug::fromString($model->slug),
            plan: TenantPlan::fromString($model->plan),
        );
    }
}
