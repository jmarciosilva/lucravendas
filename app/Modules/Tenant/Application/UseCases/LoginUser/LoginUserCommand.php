<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Application\UseCases\LoginUser;

/**
 * Dados de entrada para autenticação de usuário.
 */
final class LoginUserCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $deviceName = 'api',
    ) {}
}
