<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Cria as roles da plataforma LucraVendas.
 *
 * super_admin  → equipe LucraOne, acesso total ao painel central
 * tenant_admin → dono da loja, gerencia seu próprio catálogo e pedidos
 * customer     → cliente final de uma loja
 */
final class RolesAndPermissionsSeeder extends Seeder
{
    /** @var list<string> */
    private const ROLES = [
        'super_admin',
        'tenant_admin',
        'customer',
    ];

    public function run(): void
    {
        // Garante que o cache de permissões não interfira no seeder
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::ROLES as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'sanctum']);
        }
    }
}
