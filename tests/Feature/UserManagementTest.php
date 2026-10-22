<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Gestor',
            'email' => 'admin.gestor@sigepei.edu.br',
            'cargo' => 'NAI',
            'is_admin' => true,
            'is_active' => true,
        ]);
    }

    public function test_usuarios_index_renders_with_users_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('usuarios.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Usuarios/Index')
            ->has('users', 1)
            ->where('users.0.email', 'admin.gestor@sigepei.edu.br')
        );
    }

    public function test_usuarios_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('usuarios.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Usuarios/Create')
        );
    }

    public function test_usuario_can_be_stored(): void
    {
        $payload = [
            'name' => 'Professora Joana',
            'email' => 'joana@sigepei.edu.br',
            'cargo' => 'Professor',
            'is_admin' => false,
            'password' => 'secret123',
        ];

        $response = $this->actingAs($this->admin)->post(route('usuarios.store'), $payload);

        $response->assertRedirect(route('usuarios.index'));

        $user = User::where('email', 'joana@sigepei.edu.br')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Professora Joana', $user->name);
        $this->assertEquals('Professor', $user->cargo);
        $this->assertFalse((bool) $user->is_admin);
        $this->assertTrue((bool) $user->is_active);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_usuario_store_validates_email_uniqueness_and_cargo(): void
    {
        $response = $this->actingAs($this->admin)->post(route('usuarios.store'), [
            'name' => 'Duplicado',
            'email' => 'admin.gestor@sigepei.edu.br', // e-mail já existente
            'cargo' => 'CargoInvalido',
            'is_admin' => false,
            'password' => '123', // tamanho mínimo de 6 caracteres
        ]);

        $response->assertSessionHasErrors(['email', 'cargo', 'password']);
    }

    public function test_usuario_can_be_updated_without_changing_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Carlos Antigo',
            'email' => 'carlos@sigepei.edu.br',
            'cargo' => 'Professor',
            'is_admin' => false,
            'password' => Hash::make('senha_antiga'),
        ]);

        $response = $this->actingAs($this->admin)->put(route('usuarios.update', $user->id), [
            'name' => 'Carlos Atualizado',
            'email' => 'carlos.novo@sigepei.edu.br',
            'cargo' => 'NAI',
            'is_admin' => true,
            'password' => '', // senha vazia preserva a senha anterior
        ]);

        $response->assertRedirect(route('usuarios.index'));

        $user->refresh();
        $this->assertEquals('Carlos Atualizado', $user->name);
        $this->assertEquals('carlos.novo@sigepei.edu.br', $user->email);
        $this->assertEquals('NAI', $user->cargo);
        $this->assertTrue((bool) $user->is_admin);
        $this->assertTrue(Hash::check('senha_antiga', $user->password));
    }

    public function test_usuario_can_be_updated_with_new_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('senha_velha'),
        ]);

        $response = $this->actingAs($this->admin)->put(route('usuarios.update', $user->id), [
            'name' => $user->name,
            'email' => $user->email,
            'cargo' => $user->cargo ?? 'Professor',
            'is_admin' => false,
            'password' => 'nova_senha_segura',
        ]);

        $response->assertRedirect(route('usuarios.index'));

        $user->refresh();
        $this->assertTrue(Hash::check('nova_senha_segura', $user->password));
    }

    public function test_admin_cannot_demote_themselves_from_admin(): void
    {
        $response = $this->actingAs($this->admin)->put(route('usuarios.update', $this->admin->id), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'cargo' => $this->admin->cargo,
            'is_admin' => false, // tentativa de remover o próprio privilégio de administrador
        ]);

        $response->assertRedirect(route('usuarios.index'));

        $this->admin->refresh();
        $this->assertTrue((bool) $this->admin->is_admin);
    }

    public function test_user_cannot_inactivate_themselves(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('usuarios.destroy', $this->admin->id));

        $response->assertRedirect(route('usuarios.index'));
        $response->assertSessionHas('error');

        $this->assertTrue((bool) $this->admin->fresh()->is_active);
    }

    public function test_admin_can_inactivate_other_user(): void
    {
        $otherUser = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->delete(route('usuarios.destroy', $otherUser->id));

        $response->assertRedirect(route('usuarios.index'));
        $response->assertSessionHas('success');

        $this->assertFalse((bool) $otherUser->fresh()->is_active);
    }
}
