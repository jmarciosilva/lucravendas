<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Tenant\Application\UseCases\LoginUser\LoginUserCommand;
use App\Modules\Tenant\Application\UseCases\LoginUser\LoginUserHandler;
use App\Modules\Tenant\Application\UseCases\RegisterUser\RegisterUserCommand;
use App\Modules\Tenant\Application\UseCases\RegisterUser\RegisterUserHandler;
use App\Modules\Tenant\Presentation\Requests\LoginRequest;
use App\Modules\Tenant\Presentation\Requests\RegisterRequest;
use App\Modules\Tenant\Presentation\Resources\AuthResource;
use App\Modules\Tenant\Presentation\Resources\UserResource;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Ponto de entrada HTTP para autenticação da API.
 *
 * Responsabilidade única: validar a entrada (via FormRequest),
 * delegar ao Use Case correspondente e formatar a resposta.
 * Nenhuma regra de negócio deve residir aqui.
 */
final class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterUserHandler $registerHandler,
        private readonly LoginUserHandler $loginHandler,
    ) {}

    /**
     * Registra um novo usuário e retorna token de acesso.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $output = $this->registerHandler->handle(new RegisterUserCommand(
                name: $request->validated('name'),
                email: $request->validated('email'),
                password: $request->validated('password'),
                phone: $request->validated('phone'),
            ));

            $user = User::findOrFail($output->id);

            return (new AuthResource($user))
                ->withToken($output->token)
                ->response()
                ->setStatusCode(201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao registrar usuário.'], 500);
        }
    }

    /**
     * Autentica um usuário existente e retorna token de acesso.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $output = $this->loginHandler->handle(new LoginUserCommand(
                email: $request->validated('email'),
                password: $request->validated('password'),
                deviceName: $request->validated('device_name', 'api'),
            ));

            $user = User::findOrFail($output->id);

            return (new AuthResource($user))
                ->withToken($output->token)
                ->response();
        } catch (AuthenticationException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao realizar login.'], 500);
        }
    }

    /**
     * Revoga o token atual do usuário autenticado.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logout realizado com sucesso.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao realizar logout.'], 500);
        }
    }

    /**
     * Retorna os dados do usuário autenticado.
     */
    public function me(Request $request): JsonResponse
    {
        try {
            return response()->json(['data' => new UserResource($request->user())]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao buscar dados do usuário.'], 500);
        }
    }
}
