<?php

declare(strict_types=1);

use App\Models\User;

describe('Login de usuário', function (): void {

    it('deve autenticar com credenciais válidas e retornar token', function (): void {
        User::factory()->create([
            'email'  => 'usuario@example.com',
            'password' => bcrypt('minhasenha'),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'usuario@example.com',
            'password' => 'minhasenha',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);
    });

    it('deve retornar 401 com credenciais inválidas', function (): void {
        User::factory()->create([
            'email'  => 'usuario@example.com',
            'password' => bcrypt('senha-correta'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'usuario@example.com',
            'password' => 'senha-errada',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment(['message' => 'Credenciais inválidas.']);
    });

    it('deve retornar 401 para usuário inexistente', function (): void {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'naoexiste@example.com',
            'password' => 'qualquersenha',
        ]);

        $response->assertStatus(401);
    });

    it('deve realizar logout e revogar token', function (): void {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Logout realizado com sucesso.']);

        // Verifica que o token foi removido do banco — comportamento que importa
        $this->assertDatabaseCount('personal_access_tokens', 0);
    });

    it('deve retornar dados do usuário autenticado no endpoint me', function (): void {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    });

});
