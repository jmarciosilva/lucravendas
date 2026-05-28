<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Domain\Repositories;

use App\Modules\Tenant\Domain\Entities\Tenant;
use App\Modules\Tenant\Domain\ValueObjects\TenantId;
use App\Modules\Tenant\Domain\ValueObjects\TenantSlug;

/**
 * Contrato de persistência para a entidade Tenant.
 *
 * A implementação concreta vive na camada Infrastructure. O domínio
 * nunca sabe como os dados são persistidos — somente o que pode ser feito.
 */
interface TenantRepositoryInterface
{
    /** Persiste um tenant novo ou atualiza um existente. */
    public function save(Tenant $tenant): void;

    public function findById(TenantId $id): ?Tenant;

    public function findBySlug(TenantSlug $slug): ?Tenant;

    /** Verifica se já existe um tenant com o slug fornecido. */
    public function existsBySlug(TenantSlug $slug): bool;
}
