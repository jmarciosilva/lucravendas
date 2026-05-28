<?php

declare(strict_types=1);

/*
 * Configuração global do Pest para o projeto LucraVendas.
 *
 * Todos os testes de Feature herdam do TestCase customizado (que semeia as roles),
 * garantindo acesso ao container de IoC, banco em memória e helpers HTTP.
 */

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
 * Helper global para criar um usuário autenticado com role específica nos testes.
 */
function loginComo(string $role = 'customer'): \App\Models\User
{
    $user = \App\Models\User::factory()->create();
    $user->assignRole($role);

    return $user;
}
