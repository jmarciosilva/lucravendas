<?php

declare(strict_types=1);

use App\Modules\Tenant\Domain\Entities\Tenant;
use App\Modules\Tenant\Domain\Events\TenantCreated;
use App\Modules\Tenant\Domain\ValueObjects\TenantId;
use App\Modules\Tenant\Domain\ValueObjects\TenantPlan;
use App\Modules\Tenant\Domain\ValueObjects\TenantSlug;

describe('Entidade Tenant', function (): void {

    it('deve criar um tenant com status ativo e plano gratuito por padrão', function (): void {
        $tenant = Tenant::create(
            id: TenantId::fromString('uuid-teste-001'),
            name: 'Loja da Maria',
            slug: TenantSlug::fromString('loja-da-maria'),
        );

        expect($tenant->status())->toBe('active')
            ->and($tenant->plan()->value())->toBe(TenantPlan::FREE)
            ->and($tenant->isActive())->toBeTrue();
    });

    it('deve registrar evento TenantCreated ao criar tenant', function (): void {
        $tenant = Tenant::create(
            id: TenantId::fromString('uuid-teste-002'),
            name: 'Loja do João',
            slug: TenantSlug::fromString('loja-do-joao'),
        );

        $events = $tenant->pullDomainEvents();

        expect($events)->toHaveCount(1)
            ->and($events[0])->toBeInstanceOf(TenantCreated::class)
            ->and($events[0]->tenant)->toBe($tenant);
    });

    it('deve limpar eventos de domínio após pullDomainEvents', function (): void {
        $tenant = Tenant::create(
            id: TenantId::fromString('uuid-teste-003'),
            name: 'Loja Teste',
            slug: TenantSlug::fromString('loja-teste'),
        );

        $tenant->pullDomainEvents();

        expect($tenant->pullDomainEvents())->toBeEmpty();
    });

    it('deve suspender um tenant ativo', function (): void {
        $tenant = Tenant::create(
            id: TenantId::fromString('uuid-teste-004'),
            name: 'Loja Suspensa',
            slug: TenantSlug::fromString('loja-suspensa'),
        );
        $tenant->pullDomainEvents();

        $tenant->suspend();

        expect($tenant->isActive())->toBeFalse()
            ->and($tenant->status())->toBe('suspended');
    });

    it('não deve mudar status ao suspender tenant já suspenso', function (): void {
        $tenant = Tenant::create(
            id: TenantId::fromString('uuid-teste-005'),
            name: 'Loja',
            slug: TenantSlug::fromString('loja'),
        );
        $tenant->pullDomainEvents();
        $tenant->suspend();

        $tenant->suspend();

        expect($tenant->status())->toBe('suspended');
    });

    it('deve atualizar o plano do tenant', function (): void {
        $tenant = Tenant::create(
            id: TenantId::fromString('uuid-teste-006'),
            name: 'Loja Upgrade',
            slug: TenantSlug::fromString('loja-upgrade'),
        );
        $tenant->pullDomainEvents();

        $tenant->upgradePlan(TenantPlan::fromString(TenantPlan::GROWTH));

        expect($tenant->plan()->value())->toBe(TenantPlan::GROWTH);
    });

});

describe('Value Object TenantSlug', function (): void {

    it('deve lançar exceção para slug com menos de 3 caracteres', function (): void {
        TenantSlug::fromString('ab');
    })->throws(\InvalidArgumentException::class);

    it('deve lançar exceção para slug com caracteres inválidos', function (): void {
        TenantSlug::fromString('Loja Inválida!');
    })->throws(\InvalidArgumentException::class);

    it('deve normalizar o slug para letras minúsculas', function (): void {
        $slug = TenantSlug::fromString('MinhaLoja');
        expect($slug->value())->toBe('minhaloja');
    });

    it('deve aceitar slug válido com hífens', function (): void {
        $slug = TenantSlug::fromString('minha-loja-online');
        expect($slug->value())->toBe('minha-loja-online');
    });

});

describe('Value Object TenantPlan', function (): void {

    it('deve lançar exceção para plano inválido', function (): void {
        TenantPlan::fromString('platinum');
    })->throws(\InvalidArgumentException::class);

    it('deve criar plano gratuito via factory method', function (): void {
        $plan = TenantPlan::free();
        expect($plan->isFree())->toBeTrue();
    });

});
