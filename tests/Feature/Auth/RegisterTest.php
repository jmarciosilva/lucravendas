<?php

declare(strict_types=1);

use App\Models\User;

describe('Registro de usuário', function (): void {

    it('deve registrar um novo usuário e retornar token', function (): void {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'João Silva',
            'email'                 => 'joao@example.com',
            'password'              => 'senha12345',
            'password_confirmation' => 'senha12345',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'roles'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'joao@example.com']);
    });

    it('deve falhar quando e-mail já está cadastrado', function (): void {
        User::factory()->create(['email' => 'existente@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Maria',
            'email'                 => 'existente@example.com',
            'password'              => 'senha12345',
            'password_confirmation' => 'senha12345',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Este e-mail já está cadastrado.']);
    });

    it('deve falhar com dados inválidos', function (): void {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'     => '',
            'email'    => 'email-invalido',
            'password' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    });

    it('deve atribuir role customer por padrão ao registrar', function (): void {
        $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Cliente Padrão',
            'email'                 => 'cliente@example.com',
            'password'              => 'senha12345',
            'password_confirmation' => 'senha12345',
        ]);

        $user = User::where('email', 'cliente@example.com')->first();

        expect($user)->not->toBeNull()
            ->and($user->hasRole('customer'))->toBeTrue();
    });

});
