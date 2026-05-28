<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Domain\Entities;

use App\Modules\Tenant\Domain\Events\TenantCreated;
use App\Modules\Tenant\Domain\ValueObjects\TenantId;
use App\Modules\Tenant\Domain\ValueObjects\TenantPlan;
use App\Modules\Tenant\Domain\ValueObjects\TenantSlug;

/**
 * Entidade raiz do módulo Tenant.
 *
 * Representa uma loja (tenant) na plataforma LucraVendas. Toda a lógica
 * de negócio relacionada ao ciclo de vida de um tenant vive aqui,
 * nunca em controllers ou models Eloquent.
 */
final class Tenant
{
    /** @var list<object> Eventos de domínio pendentes de despacho */
    private array $domainEvents = [];

    public function __construct(
        private readonly TenantId $id,
        private readonly TenantSlug $slug,
        private string $name,
        private TenantPlan $plan,
        private string $status,
    ) {}

    /**
     * Cria um novo tenant com plano gratuito por padrão.
     * Registra o evento de domínio TenantCreated para notificar outros módulos.
     */
    public static function create(
        TenantId $id,
        string $name,
        TenantSlug $slug,
        TenantPlan $plan = null,
    ): self {
        $tenant = new self(
            id: $id,
            slug: $slug,
            name: $name,
            plan: $plan ?? TenantPlan::free(),
            status: 'active',
        );

        $tenant->recordEvent(new TenantCreated($tenant));

        return $tenant;
    }

    public function suspend(): void
    {
        // Impede suspensão dupla para manter consistência no histórico de status
        if ($this->status === 'suspended') {
            return;
        }

        $this->status = 'suspended';
    }

    public function activate(): void
    {
        $this->status = 'active';
    }

    public function upgradePlan(TenantPlan $newPlan): void
    {
        $this->plan = $newPlan;
    }

    public function id(): TenantId
    {
        return $this->id;
    }

    public function slug(): TenantSlug
    {
        return $this->slug;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function plan(): TenantPlan
    {
        return $this->plan;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** @return list<object> */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}
