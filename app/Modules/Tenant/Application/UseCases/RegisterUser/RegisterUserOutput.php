<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Application\UseCases\RegisterUser;

/**
 * Dados de retorno após registro bem-sucedido.
 */
final class RegisterUserOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $role,
        public readonly string $token,
    ) {}
}
