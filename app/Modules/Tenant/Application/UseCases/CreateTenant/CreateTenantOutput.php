<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Application\UseCases\CreateTenant;

/**
 * DTO de saída para o caso de uso de criação de tenant.
 *
 * Desacopla a camada Presentation do domínio: o controller nunca
 * manipula a entidade Tenant diretamente.
 */
final class CreateTenantOutput
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $plan,
        public readonly string $status,
    ) {}
}
