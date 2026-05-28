<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Presentation\Commands;

use App\Modules\Tenant\Application\UseCases\CreateTenant\CreateTenantCommand as CreateTenantDto;
use App\Modules\Tenant\Application\UseCases\CreateTenant\CreateTenantHandler;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Comando Artisan para provisionamento de tenants via CLI.
 *
 * Usado em ambientes de desenvolvimento e por automações de onboarding.
 * Em produção, prefira criar tenants via painel admin (Filament).
 */
final class CreateTenantCommand extends Command
{
    protected $signature = 'tenant:create
                            {name : Nome da loja}
                            {slug : Slug único (usado no subdomínio)}
                            {--plan=free : Plano contratado (free, starter, growth, enterprise)}';

    protected $description = 'Provisiona um novo tenant (loja) na plataforma LucraVendas';

    public function handle(CreateTenantHandler $handler): int
    {
        $name = (string) $this->argument('name');
        $slug = (string) $this->argument('slug');
        $plan = (string) $this->option('plan');

        try {
            $output = $handler->handle(new CreateTenantDto(
                name: $name,
                slug: $slug,
                plan: $plan,
            ));

            $this->info("Tenant criado com sucesso!");
            $this->table(
                ['ID', 'Nome', 'Slug', 'Plano', 'Status'],
                [[$output->id, $output->name, $output->slug, $output->plan, $output->status]]
            );

            return self::SUCCESS;
        } catch (RuntimeException $e) {
            $this->error("Erro ao criar tenant: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
