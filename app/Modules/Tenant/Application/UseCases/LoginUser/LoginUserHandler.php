<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Application\UseCases\LoginUser;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

/**
 * Autentica um usuário e emite um token Sanctum.
 *
 * Tokens antigos não são revogados aqui para suportar múltiplos dispositivos.
 * A revogação explícita ocorre no logout ou via painel admin.
 */
final class LoginUserHandler
{
    public function handle(LoginUserCommand $command): LoginUserOutput
    {
        $user = User::where('email', $command->email)->first();

        if (! $user || ! Hash::check($command->password, $user->password)) {
            throw new AuthenticationException('Credenciais inválidas.');
        }

        if ($user->status !== 'active') {
            throw new AuthenticationException('Conta inativa ou suspensa.');
        }

        $token = $user->createToken($command->deviceName)->plainTextToken;

        return new LoginUserOutput(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            token: $token,
        );
    }
}
