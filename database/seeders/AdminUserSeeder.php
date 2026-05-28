<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Cria o usuário administrador padrão da plataforma LucraOne.
 *
 * ATENÇÃO: altere a senha imediatamente após o primeiro deploy em produção.
 * Este seeder deve rodar apenas uma vez — a lógica firstOrCreate garante idempotência.
 */
final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'jmarciosilva@gmail.com'],
            [
                'name'              => 'José Marcio Ferreira da Silva',
                'password'          => Hash::make('12345678'),
                'email_verified_at' => now(),
                'status'            => 'active',
            ]
        );

        $admin->assignRole('super_admin');

        $this->command->info("Usuário admin criado: jmarciosilva@gmail.com");
    }
}
