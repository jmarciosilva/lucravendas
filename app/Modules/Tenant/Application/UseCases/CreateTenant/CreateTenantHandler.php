<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Application\UseCases\CreateTenant;

use App\Modules\Tenant\Domain\Entities\Tenant;
use App\Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;
use App\Modules\Tenant\Domain\ValueObjects\TenantId;
use App\Modules\Tenant\Domain\ValueObjects\TenantPlan;
use App\Modules\Tenant\Domain\ValueObjects\TenantSlug;
use Illuminate\Contracts\Events\Dispatcher;
use RuntimeException;

/**
 * Orquestra a criação de um novo tenant.
 *
 * Responsabilidade única: coordenar domínio e persistência.
 * Regras de negócio ficam nas entidades e value objects do domínio.
 */
final class CreateTenantHandler
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
        private readonly Dispatcher $events,
    ) {}

    public function handle(CreateTenantCommand $command): CreateTenantOutput
    {
        $slug = TenantSlug::fromString($command->slug);

        if ($this->repository->existsBySlug($slug)) {
            throw new RuntimeException("O slug '{$command->slug}' já está em uso por outro tenant.");
        }

        // O ID é gerado aqui antes de persistir, para que o evento de domínio
        // já carregue o identificador final do tenant
        $id = TenantId::fromString((string) \Illuminate\Support\Str::uuid());

        $tenant = Tenant::create(
            id: $id,
            name: $command->name,
            slug: $slug,
            plan: TenantPlan::fromString($command->plan),
        );

        $this->repository->save($tenant);

        // Despacha eventos de domínio acumulados pela entidade
        foreach ($tenant->pullDomainEvents() as $event) {
            $this->events->dispatch($event);
        }

        return new CreateTenantOutput(
            id: $tenant->id()->value(),
            name: $tenant->name(),
            slug: $tenant->slug()->value(),
            plan: $tenant->plan()->value(),
            status: $tenant->status(),
        );
    }
}
