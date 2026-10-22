<?php

namespace Tests\Feature;

use App\Models\Deficiencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeficienciaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'cargo' => 'NAI',
            'is_admin' => true,
        ]);
    }

    public function test_deficiencia_index_renders(): void
    {
        Deficiencia::create([
            'codigo' => 'F84.0',
            'nome' => 'Transtorno do Espectro Autista',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('deficiencias.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Deficiencias/Index')
            ->has('deficiencias', 1)
            ->where('deficiencias.0.codigo', 'F84.0')
            ->where('deficiencias.0.nome', 'Transtorno do Espectro Autista')
        );
    }

    public function test_deficiencia_create_page_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('deficiencias.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Deficiencias/Create')
        );
    }

    public function test_deficiencia_can_be_stored(): void
    {
        $payload = [
            'codigo' => 'H90',
            'nome' => 'Perda de audição condutiva e neurossensorial',
        ];

        $response = $this->actingAs($this->user)->post(route('deficiencias.store'), $payload);

        $response->assertRedirect(route('deficiencias.index'));

        $this->assertDatabaseHas('deficiencias', [
            'codigo' => 'H90',
            'nome' => 'Perda de audição condutiva e neurossensorial',
            'is_active' => true,
        ]);
    }

    public function test_deficiencia_store_validation_requires_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('deficiencias.store'), [
            'codigo' => '',
            'nome' => '',
        ]);

        $response->assertSessionHasErrors(['codigo', 'nome']);
    }

    public function test_deficiencia_can_be_updated(): void
    {
        $def = Deficiencia::create([
            'codigo' => 'F90',
            'nome' => 'TDAH',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->put(route('deficiencias.update', $def->id), [
            'codigo' => 'F90.0',
            'nome' => 'Transtorno do Déficit de Atenção com Hiperatividade',
        ]);

        $response->assertRedirect(route('deficiencias.index'));

        $def->refresh();
        $this->assertEquals('F90.0', $def->codigo);
        $this->assertEquals('Transtorno do Déficit de Atenção com Hiperatividade', $def->nome);
    }

    public function test_deficiencia_active_status_can_be_toggled(): void
    {
        $def = Deficiencia::create([
            'codigo' => 'G40',
            'nome' => 'Epilepsia',
            'is_active' => true,
        ]);

        // inativação da deficiência
        $resInativar = $this->actingAs($this->user)->delete(route('deficiencias.destroy', $def->id));
        $resInativar->assertRedirect(route('deficiencias.index'));
        $this->assertFalse((bool) $def->fresh()->is_active);

        // reativação da deficiência
        $resReativar = $this->actingAs($this->user)->delete(route('deficiencias.destroy', $def->id));
        $resReativar->assertRedirect(route('deficiencias.index'));
        $this->assertTrue((bool) $def->fresh()->is_active);
    }
}
