<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Application\UseCases\CreateTenant;

/**
 * DTO de entrada para o caso de uso de criação de tenant.
 *
 * Imutável por design: o comando representa a intenção já validada;
 * alterações não fazem sentido após a construção.
 */
final class CreateTenantCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $plan = 'free',
    ) {}
}
