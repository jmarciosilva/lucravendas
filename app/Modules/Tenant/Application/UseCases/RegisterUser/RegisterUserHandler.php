<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Application\UseCases\RegisterUser;

use App\Models\User;
use RuntimeException;

/**
 * Orquestra o registro de um novo usuário na plataforma.
 *
 * O token Sanctum é criado imediatamente no registro para
 * eliminar um round-trip de login desnecessário na UX mobile.
 */
final class RegisterUserHandler
{
    public function handle(RegisterUserCommand $command): RegisterUserOutput
    {
        if (User::where('email', $command->email)->exists()) {
            throw new RuntimeException('Este e-mail já está cadastrado.');
        }

        $user = User::create([
            'name'      => $command->name,
            'email'     => $command->email,
            'password'  => $command->password,
            'phone'     => $command->phone,
            'tenant_id' => $command->tenantId,
            'status'    => 'active',
        ]);

        $user->assignRole($command->role);

        $token = $user->createToken('api')->plainTextToken;

        return new RegisterUserOutput(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            role: $command->role,
            token: $token,
        );
    }
}
