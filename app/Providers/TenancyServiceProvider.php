<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;
use App\Modules\Tenant\Infrastructure\Http\Middleware\InitializeTenancyByHeader;
use App\Modules\Tenant\Infrastructure\Repositories\EloquentTenantRepository;
use App\Modules\Tenant\Presentation\Commands\CreateTenantCommand;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;

/**
 * Provider central do módulo de multi-tenancy.
 *
 * Registra eventos, listeners, middlewares e os bindings de repositório
 * do módulo Tenant. Configuração de rotas tenant fica em routes/tenant.php.
 */
class TenancyServiceProvider extends ServiceProvider
{
    public static string $controllerNamespace = '';

    public function register(): void
    {
        // Binding da interface de repositório para a implementação Eloquent
        $this->app->bind(
            TenantRepositoryInterface::class,
            EloquentTenantRepository::class,
        );
    }

    public function boot(): void
    {
        $this->bootEvents();
        $this->mapRoutes();
        $this->makeTenancyMiddlewareHighestPriority();

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateTenantCommand::class,
            ]);
        }
    }

    /**
     * Retorna o mapa evento → listeners/pipelines do ciclo de vida do tenant.
     *
     * @return array<class-string, list<mixed>>
     */
    public function events(): array
    {
        return [
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class  => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                    // Jobs\SeedDatabase::class, // habilitar quando o seeder do tenant estiver pronto
                ])->send(fn (Events\TenantCreated $event) => $event->tenant)
                  ->shouldBeQueued(false),
            ],
            Events\SavingTenant::class   => [],
            Events\TenantSaved::class    => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class  => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class  => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(fn (Events\TenantDeleted $event) => $event->tenant)
                  ->shouldBeQueued(false),
            ],

            Events\CreatingDomain::class => [],
            Events\DomainCreated::class  => [],
            Events\SavingDomain::class   => [],
            Events\DomainSaved::class    => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class  => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class  => [],

            Events\DatabaseCreated::class    => [],
            Events\DatabaseMigrated::class   => [],
            Events\DatabaseSeeded::class     => [],
            Events\DatabaseRolledBack::class => [],
            Events\DatabaseDeleted::class    => [],

            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class  => [
                Listeners\BootstrapTenancy::class,
            ],

            Events\EndingTenancy::class   => [],
            Events\TenancyEnded::class    => [
                Listeners\RevertToCentralContext::class,
            ],

            Events\BootstrappingTenancy::class    => [],
            Events\TenancyBootstrapped::class     => [],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class  => [],

            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],
            Events\SyncedResourceChangedInForeignDatabase::class => [],
        ];
    }

    protected function bootEvents(): void
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes(): void
    {
        $this->app->booted(function (): void {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant.php'));
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority(): void
    {
        $tenancyMiddleware = [
            Middleware\PreventAccessFromCentralDomains::class,
            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
            // Header X-Tenant-ID para clientes mobile e integrações externas
            InitializeTenancyByHeader::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }
}
