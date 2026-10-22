<?php

namespace Tests\Feature;

use App\Models\AnoLetivo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarioTest extends TestCase
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

    public function test_calendario_index_renders_with_anos(): void
    {
        AnoLetivo::create([
            'ano' => 2026,
            'concluido' => false,
            'trimestre1_inicio' => '2026-02-09',
            'trimestre1_fim' => '2026-05-15',
        ]);

        $response = $this->actingAs($this->user)->get(route('calendario.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Calendario/Index')
            ->has('anosLetivos', 1)
            ->where('anosLetivos.0.ano', 2026)
            ->where('anosLetivos.0.concluido', false)
        );
    }

    public function test_calendario_update_saves_batch_records(): void
    {
        $response = $this->actingAs($this->user)->put(route('calendario.update'), [
            'anos' => [
                [
                    'ano' => 2026,
                    'concluido' => false,
                    'trimestre1_inicio' => '2026-02-09',
                    'trimestre1_fim' => '2026-05-15',
                    'trimestre2_inicio' => '2026-05-18',
                    'trimestre2_fim' => '2026-08-28',
                    'trimestre3_inicio' => '2026-09-01',
                    'trimestre3_fim' => '2026-12-18',
                ],
            ],
        ]);

        $response->assertRedirect(route('calendario.index'));
        $this->assertDatabaseHas('anos_letivos', [
            'ano' => 2026,
            'concluido' => false,
        ]);
    }

    public function test_calendario_concluido_cannot_be_deleted(): void
    {
        $concluido = AnoLetivo::create([
            'ano' => 2025,
            'concluido' => true,
            'trimestre1_inicio' => '2025-02-10',
            'trimestre1_fim' => '2025-05-16',
            'trimestre2_inicio' => '2025-05-19',
            'trimestre2_fim' => '2025-08-29',
            'trimestre3_inicio' => '2025-09-01',
            'trimestre3_fim' => '2025-12-19',
        ]);

        $response = $this->actingAs($this->user)->delete(route('calendario.destroy', $concluido->id));

        $response->assertSessionHasErrors('calendario');
        $this->assertDatabaseHas('anos_letivos', ['id' => $concluido->id]);
    }

    public function test_calendario_nao_concluido_can_be_deleted(): void
    {
        $ativo = AnoLetivo::create([
            'ano' => 2027,
            'concluido' => false,
        ]);

        $response = $this->actingAs($this->user)->delete(route('calendario.destroy', $ativo->id));

        $response->assertRedirect(route('calendario.index'));
        $this->assertDatabaseMissing('anos_letivos', ['id' => $ativo->id]);
    }
}
