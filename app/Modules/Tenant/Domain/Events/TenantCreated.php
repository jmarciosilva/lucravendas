<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Domain\Events;

use App\Modules\Tenant\Domain\Entities\Tenant;

/**
 * Evento disparado quando um novo tenant é criado com sucesso.
 *
 * Outros módulos (Marketing, Admin) podem reagir a este evento para
 * provisionar recursos iniciais sem acoplar ao módulo Tenant.
 */
final class TenantCreated
{
    public function __construct(
        public readonly Tenant $tenant,
    ) {}
}
