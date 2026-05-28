<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder raiz — orquestra a ordem de execução dos seeders.
 *
 * Em produção, apenas RolesAndPermissionsSeeder e AdminUserSeeder devem rodar.
 * Os demais seeders são exclusivos para desenvolvimento e staging.
 */
final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
