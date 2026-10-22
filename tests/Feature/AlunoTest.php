<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Deficiencia;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlunoTest extends TestCase
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

    public function test_aluno_index_renders_with_students_list(): void
    {
        $aluno = Aluno::create([
            'nome' => 'Ana Clara Santos',
            'matricula' => '20261001',
            'data_nascimento' => '2008-04-10',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('alunos.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Alunos/Index')
            ->has('alunos', 1)
            ->where('alunos.0.nome', 'Ana Clara Santos')
            ->where('alunos.0.matricula', '20261001')
            ->where('alunos.0.ativo', true)
        );
    }

    public function test_aluno_create_page_renders_with_deficiencias_and_turmas(): void
    {
        $def = Deficiencia::create([
            'codigo' => 'F84.0',
            'nome' => 'Autismo',
            'is_active' => true,
        ]);

        $turma = Turma::create([
            'nome_curso' => 'Edificações',
            'ano_letivo' => 2026,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('alunos.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Alunos/Create')
            ->has('deficienciasDisponiveis', 1)
            ->where('deficienciasDisponiveis.0.codigo', 'F84.0')
            ->has('turmasDisponiveis', 1)
            ->where('turmasDisponiveis.0.nome_curso', 'Edificações')
        );
    }

    public function test_aluno_can_be_stored_with_deficiencias_and_turma(): void
    {
        $def = Deficiencia::create([
            'codigo' => 'H54.0',
            'nome' => 'Cegueira',
            'is_active' => true,
        ]);

        $turma = Turma::create([
            'nome_curso' => 'Informática para Internet',
            'ano_letivo' => 2026,
            'is_active' => true,
        ]);

        $payload = [
            'nome' => 'Gabriel Monteiro',
            'matricula' => '20262002',
            'data_nascimento' => '2007-11-20',
            'deficiencias' => [$def->id],
            'turma_id' => $turma->id,
        ];

        $response = $this->actingAs($this->user)->post(route('alunos.store'), $payload);

        $aluno = Aluno::where('matricula', '20262002')->first();
        $this->assertNotNull($aluno);
        $this->assertEquals('Gabriel Monteiro', $aluno->nome);

        $response->assertRedirect(route('alunos.show', $aluno->id));

        $this->assertDatabaseHas('alunos', [
            'matricula' => '20262002',
            'nome' => 'Gabriel Monteiro',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('aluno_deficiencia', [
            'aluno_id' => $aluno->id,
            'deficiencia_id' => $def->id,
        ]);

        $this->assertDatabaseHas('aluno_turma', [
            'aluno_id' => $aluno->id,
            'turma_id' => $turma->id,
            'ano_letivo' => 2026,
            'is_atual' => true,
        ]);
    }

    public function test_aluno_store_validation_fails_for_duplicate_matricula(): void
    {
        Aluno::create([
            'nome' => 'Aluno Existente',
            'matricula' => '20260001',
            'data_nascimento' => '2008-01-01',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->post(route('alunos.store'), [
            'nome' => 'Novo Aluno Repetido',
            'matricula' => '20260001',
            'data_nascimento' => '2008-02-02',
        ]);

        $response->assertSessionHasErrors(['matricula']);
    }

    public function test_aluno_show_renders_complete_details(): void
    {
        $aluno = Aluno::create([
            'nome' => 'Beatriz Lima',
            'matricula' => '20263003',
            'data_nascimento' => '2009-08-14',
            'is_active' => true,
        ]);

        $turma = Turma::create([
            'nome_curso' => 'Agropecuária',
            'ano_letivo' => 2026,
            'is_active' => true,
        ]);

        $aluno->turmas()->attach($turma->id, ['ano_letivo' => 2026, 'is_atual' => true]);

        $response = $this->actingAs($this->user)->get(route('alunos.show', $aluno->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Alunos/Show')
            ->where('aluno.nome', 'Beatriz Lima')
            ->where('aluno.matricula', '20263003')
            ->where('aluno.turma_atual.nome', 'Agropecuária')
            ->has('turmasDisponiveis')
        );
    }

    public function test_aluno_can_be_updated_and_sync_deficiencias(): void
    {
        $def1 = Deficiencia::create(['codigo' => 'CID-1', 'nome' => 'Deficiência Auditiva']);
        $def2 = Deficiencia::create(['codigo' => 'CID-2', 'nome' => 'Baixa Visão']);

        $aluno = Aluno::create([
            'nome' => 'Carlos Eduardo',
            'matricula' => '20264004',
            'data_nascimento' => '2008-03-12',
            'is_active' => true,
        ]);
        $aluno->deficiencias()->attach($def1->id);

        $response = $this->actingAs($this->user)->put(route('alunos.update', $aluno->id), [
            'nome' => 'Carlos Eduardo Atualizado',
            'matricula' => '20264004', // mantém a mesma matrícula
            'data_nascimento' => '2008-03-12',
            'deficiencias' => [$def2->id],
        ]);

        $response->assertRedirect(route('alunos.show', $aluno->id));

        $aluno->refresh();
        $this->assertEquals('Carlos Eduardo Atualizado', $aluno->nome);
        $this->assertTrue($aluno->deficiencias->contains('id', $def2->id));
        $this->assertFalse($aluno->deficiencias->contains('id', $def1->id));
    }

    public function test_aluno_active_status_can_be_toggled(): void
    {
        $aluno = Aluno::create([
            'nome' => 'Mariana Duarte',
            'matricula' => '20265005',
            'data_nascimento' => '2008-07-21',
            'is_active' => true,
        ]);

        // inativação do discente
        $resInativar = $this->actingAs($this->user)->delete(route('alunos.destroy', $aluno->id));
        $resInativar->assertRedirect(route('alunos.index'));
        $this->assertFalse((bool) $aluno->fresh()->is_active);

        // reativação do discente
        $resReativar = $this->actingAs($this->user)->delete(route('alunos.destroy', $aluno->id));
        $resReativar->assertRedirect(route('alunos.index'));
        $this->assertTrue((bool) $aluno->fresh()->is_active);
    }

    public function test_aluno_associar_turma_updates_current_turma(): void
    {
        $aluno = Aluno::create([
            'nome' => 'Felipe Alves',
            'matricula' => '20266006',
            'data_nascimento' => '2008-12-05',
            'is_active' => true,
        ]);

        $turma1 = Turma::create(['nome_curso' => '1º Ano Informática', 'ano_letivo' => 2025]);
        $turma2 = Turma::create(['nome_curso' => '2º Ano Informática', 'ano_letivo' => 2026]);

        $aluno->turmas()->attach($turma1->id, ['ano_letivo' => 2025, 'is_atual' => true]);

        $response = $this->actingAs($this->user)->post(route('alunos.associar-turma', $aluno->id), [
            'turma_id' => $turma2->id,
            'ano_letivo' => 2026,
        ]);

        $response->assertRedirect(route('alunos.show', $aluno->id));

        $aluno->refresh();
        $turmaAtual = $aluno->turmaAtual();
        $this->assertEquals($turma2->id, $turmaAtual->id);

        $turma1Pivot = $aluno->turmas()->where('turma_id', $turma1->id)->first()->pivot;
        $this->assertFalse((bool) $turma1Pivot->is_atual);
    }

    public function test_aluno_foto_upload_view_and_remove(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $aluno = Aluno::create([
            'nome' => 'Foto Teste Aluno',
            'matricula' => '20267007',
            'data_nascimento' => '2008-01-10',
            'is_active' => true,
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 300, 300);

        $response = $this->actingAs($this->user)->post(route('alunos.update', $aluno->id), [
            '_method' => 'put',
            'nome' => 'Foto Teste Aluno',
            'matricula' => '20267007',
            'data_nascimento' => '2008-01-10',
            'foto' => $file,
        ]);

        $response->assertRedirect(route('alunos.show', $aluno->id));
        $aluno->refresh();

        $this->assertNotNull($aluno->foto_path);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($aluno->foto_path);
        $this->assertNotNull($aluno->foto_url);

        // visualização da foto com autenticação
        $resFoto = $this->actingAs($this->user)->get($aluno->foto_url);
        $resFoto->assertOk();

        // remoção da foto
        $resRemover = $this->actingAs($this->user)->post(route('alunos.update', $aluno->id), [
            '_method' => 'put',
            'nome' => 'Foto Teste Aluno',
            'matricula' => '20267007',
            'data_nascimento' => '2008-01-10',
            'remover_foto' => true,
        ]);

        $resRemover->assertRedirect(route('alunos.show', $aluno->id));
        $aluno->refresh();
        $this->assertNull($aluno->foto_path);
        $this->assertNull($aluno->foto_url);
    }
}
