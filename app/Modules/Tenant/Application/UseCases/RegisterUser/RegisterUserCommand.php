<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Application\UseCases\RegisterUser;

/**
 * Dados validados de entrada para registro de novo usuário.
 */
final class RegisterUserCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $role = 'customer',
        public readonly ?string $tenantId = null,
        public readonly ?string $phone = null,
    ) {}
}
