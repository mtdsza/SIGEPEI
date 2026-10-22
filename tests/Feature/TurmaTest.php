<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurmaTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $professor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'cargo' => 'NAI',
            'is_admin' => true,
        ]);

        $this->professor = User::factory()->create([
            'cargo' => 'Professor',
            'is_admin' => false,
        ]);
    }

    public function test_turma_index_renders_with_metrics_and_list(): void
    {
        $turma = Turma::create([
            'nome_curso' => 'Técnico em Química 2026',
            'ano_letivo' => 2026,
            'is_active' => true,
        ]);

        $disciplina = Disciplina::create(['codigo' => 'QUI-01', 'nome' => 'Química Geral']);
        $turmaDisc = $turma->turmaDisciplinas()->create(['disciplina_id' => $disciplina->id]);

        $aluno = Aluno::create([
            'nome' => 'Juliana Paes',
            'matricula' => '20267007',
            'data_nascimento' => '2008-09-10',
            'is_active' => true,
        ]);
        $aluno->turmas()->attach($turma->id, ['ano_letivo' => 2026, 'is_atual' => true]);

        $response = $this->actingAs($this->admin)->get(route('turmas.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Turmas/Index')
            ->has('turmas', 1)
            ->where('turmas.0.nome', 'Técnico em Química 2026')
            ->where('turmas.0.ano', 2026)
            ->where('turmas.0.disciplinas_qtd', '1 disciplina(s)')
            ->where('turmas.0.alunos_qtd', '1 aluno(s)')
        );
    }

    public function test_turma_create_page_renders(): void
    {
        $disciplina = Disciplina::create(['codigo' => 'BIO-01', 'nome' => 'Biologia']);

        $response = $this->actingAs($this->admin)->get(route('turmas.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Turmas/Create')
            ->has('disciplinasDisponiveis')
            ->has('professoresDisponiveis')
        );
    }

    public function test_turma_can_be_stored_with_disciplinas_and_professores(): void
    {
        $disc1 = Disciplina::create(['codigo' => 'FIS-01', 'nome' => 'Física 1']);
        $disc2 = Disciplina::create(['codigo' => 'MAT-01', 'nome' => 'Matemática 1']);

        $payload = [
            'nome_curso' => 'Técnico em Mecânica',
            'ano_letivo' => 2026,
            'disciplinas' => [
                [
                    'disciplina_id' => $disc1->id,
                    'professores' => [
                        [
                            'user_id' => $this->professor->id,
                            'is_substituto' => false,
                        ],
                    ],
                ],
                [
                    'disciplina_id' => $disc2->id,
                    'professores' => [],
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('turmas.store'), $payload);

        $turma = Turma::where('nome_curso', 'Técnico em Mecânica')->first();
        $this->assertNotNull($turma);

        $response->assertRedirect(route('turmas.show', $turma->id));

        $this->assertDatabaseHas('turmas', [
            'nome_curso' => 'Técnico em Mecânica',
            'ano_letivo' => 2026,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('turma_disciplina', [
            'turma_id' => $turma->id,
            'disciplina_id' => $disc1->id,
        ]);

        $td = $turma->turmaDisciplinas()->where('disciplina_id', $disc1->id)->first();
        $this->assertDatabaseHas('turma_disciplina_professor', [
            'turma_disciplina_id' => $td->id,
            'user_id' => $this->professor->id,
            'is_substituto' => false,
        ]);
    }

    public function test_turma_show_renders_details_and_vinculated_students(): void
    {
        $turma = Turma::create([
            'nome_curso' => 'Eletrotécnica 2026',
            'ano_letivo' => 2026,
            'is_active' => true,
        ]);

        $disciplina = Disciplina::create(['codigo' => 'ELE-01', 'nome' => 'Circuitos Elétricos']);
        $td = $turma->turmaDisciplinas()->create(['disciplina_id' => $disciplina->id]);
        $td->professores()->attach($this->professor->id, ['is_substituto' => false]);

        $aluno = Aluno::create([
            'nome' => 'Renata Silveira',
            'matricula' => '20268008',
            'data_nascimento' => '2008-01-25',
            'is_active' => true,
        ]);
        $aluno->turmas()->attach($turma->id, ['ano_letivo' => 2026, 'is_atual' => true]);

        $response = $this->actingAs($this->admin)->get(route('turmas.show', $turma->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Turmas/Show')
            ->where('turma.nome_curso', 'Eletrotécnica 2026')
            ->where('turma.disciplinas.0.nome', 'Circuitos Elétricos')
            ->where('turma.disciplinas.0.professores.0.name', $this->professor->name)
            ->where('turma.alunos.0.nome', 'Renata Silveira')
        );
    }

    public function test_turma_can_be_updated(): void
    {
        $turma = Turma::create([
            'nome_curso' => 'Nome Antigo',
            'ano_letivo' => 2025,
            'is_active' => true,
        ]);

        $disc = Disciplina::create(['codigo' => 'GEO-01', 'nome' => 'Geografia']);

        $response = $this->actingAs($this->admin)->put(route('turmas.update', $turma->id), [
            'nome_curso' => 'Nome Atualizado',
            'ano_letivo' => 2026,
            'disciplinas' => [
                [
                    'disciplina_id' => $disc->id,
                    'professores' => [],
                ],
            ],
        ]);

        $response->assertRedirect(route('turmas.show', $turma->id));

        $turma->refresh();
        $this->assertEquals('Nome Atualizado', $turma->nome_curso);
        $this->assertEquals(2026, $turma->ano_letivo);
        $this->assertCount(1, $turma->turmaDisciplinas);
    }

    public function test_turma_active_status_can_be_toggled(): void
    {
        $turma = Turma::create([
            'nome_curso' => 'Turma Para Inativar',
            'ano_letivo' => 2026,
            'is_active' => true,
        ]);

        // inativação da turma
        $resInativar = $this->actingAs($this->admin)->delete(route('turmas.destroy', $turma->id));
        $resInativar->assertRedirect(route('turmas.index'));
        $this->assertFalse((bool) $turma->fresh()->is_active);

        // reativação da turma
        $resReativar = $this->actingAs($this->admin)->delete(route('turmas.destroy', $turma->id));
        $resReativar->assertRedirect(route('turmas.index'));
        $this->assertTrue((bool) $turma->fresh()->is_active);
    }
}
