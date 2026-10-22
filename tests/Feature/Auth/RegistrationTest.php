<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_redirects_to_login(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect(route('login'));
    }

    public function test_public_user_registration_endpoint_is_blocked(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_admin_can_create_new_user_via_management(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'cargo' => 'NAI',
        ]);

        $response = $this->actingAs($admin)->post('/usuarios', [
            'name' => 'Novo Professor',
            'email' => 'professor@instituicao.edu.br',
            'cargo' => 'Professor',
            'is_admin' => 0,
            'password' => 'senha123',
        ]);

        $response->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'professor@instituicao.edu.br',
            'name' => 'Novo Professor',
            'cargo' => 'Professor',
            'is_admin' => false,
        ]);
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'cargo' => 'Professor',
        ]);

        $response = $this->actingAs($user)->post('/usuarios', [
            'name' => 'Tentativa Invalida',
            'email' => 'invalido@instituicao.edu.br',
            'cargo' => 'Professor',
            'is_admin' => 0,
            'password' => 'senha123',
        ]);

        $response->assertStatus(403);
    }
}

