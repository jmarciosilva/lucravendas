<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Tenant\Application\UseCases\LoginUser\LoginUserHandler;
use App\Modules\Tenant\Application\UseCases\RegisterUser\RegisterUserHandler;
use Illuminate\Support\ServiceProvider;

/**
 * Provider principal da aplicação.
 *
 * Registra bindings explícitos dos handlers de Use Cases para que o container
 * de IoC do Laravel resolva as dependências corretamente via injeção no controller.
 */
final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RegisterUserHandler::class, RegisterUserHandler::class);
        $this->app->bind(LoginUserHandler::class, LoginUserHandler::class);
    }

    public function boot(): void {}
}
