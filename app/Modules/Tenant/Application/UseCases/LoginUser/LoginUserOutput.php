<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Application\UseCases\LoginUser;

/**
 * Dados de retorno após login bem-sucedido.
 */
final class LoginUserOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $token,
        public readonly string $tokenType = 'Bearer',
    ) {}
}
