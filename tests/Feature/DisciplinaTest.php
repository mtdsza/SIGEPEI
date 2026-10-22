<?php

namespace Tests\Feature;

use App\Models\Disciplina;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplinaTest extends TestCase
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

    public function test_disciplina_index_renders(): void
    {
        Disciplina::create([
            'codigo' => 'HIST-01',
            'nome' => 'História do Brasil',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('disciplinas.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Disciplinas/Index')
            ->has('disciplinas', 1)
            ->where('disciplinas.0.codigo', 'HIST-01')
            ->where('disciplinas.0.nome', 'História do Brasil')
        );
    }

    public function test_disciplina_create_page_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('disciplinas.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Disciplinas/Create')
        );
    }

    public function test_disciplina_can_be_stored(): void
    {
        $payload = [
            'codigo' => 'PROG-01',
            'nome' => 'Algoritmos e Estruturas de Dados',
        ];

        $response = $this->actingAs($this->user)->post(route('disciplinas.store'), $payload);

        $response->assertRedirect(route('disciplinas.index'));

        $this->assertDatabaseHas('disciplinas', [
            'codigo' => 'PROG-01',
            'nome' => 'Algoritmos e Estruturas de Dados',
            'is_active' => true,
        ]);
    }

    public function test_disciplina_store_validation_requires_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('disciplinas.store'), [
            'codigo' => '',
            'nome' => '',
        ]);

        $response->assertSessionHasErrors(['codigo', 'nome']);
    }

    public function test_disciplina_can_be_updated(): void
    {
        $disc = Disciplina::create([
            'codigo' => 'ART-01',
            'nome' => 'Artes',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->put(route('disciplinas.update', $disc->id), [
            'codigo' => 'ART-02',
            'nome' => 'Artes Visuais e Música',
        ]);

        $response->assertRedirect(route('disciplinas.index'));

        $disc->refresh();
        $this->assertEquals('ART-02', $disc->codigo);
        $this->assertEquals('Artes Visuais e Música', $disc->nome);
    }

    public function test_disciplina_active_status_can_be_toggled(): void
    {
        $disc = Disciplina::create([
            'codigo' => 'FIL-01',
            'nome' => 'Filosofia',
            'is_active' => true,
        ]);

        // inativação da disciplina
        $resInativar = $this->actingAs($this->user)->delete(route('disciplinas.destroy', $disc->id));
        $resInativar->assertRedirect(route('disciplinas.index'));
        $this->assertFalse((bool) $disc->fresh()->is_active);

        // reativação da disciplina
        $resReativar = $this->actingAs($this->user)->delete(route('disciplinas.destroy', $disc->id));
        $resReativar->assertRedirect(route('disciplinas.index'));
        $this->assertTrue((bool) $disc->fresh()->is_active);
    }
}
