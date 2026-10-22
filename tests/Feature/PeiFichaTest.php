<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Deficiencia;
use App\Models\Disciplina;
use App\Models\Pei;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeiFichaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Aluno $aluno;
    protected Turma $turma;
    protected Pei $pei;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'admin@sigepei.edu.br',
            'cargo' => 'NAI',
        ]);

        $this->turma = Turma::create([
            'nome_curso' => 'Técnico em Informática',
            'ano_letivo' => 2026,
            'is_active' => true,
        ]);

        $this->aluno = Aluno::create([
            'nome' => 'Lucas Gabriel Ferreira',
            'matricula' => '2026101001',
            'data_nascimento' => '2008-05-15',
            'is_active' => true,
        ]);

        $this->aluno->turmas()->attach($this->turma->id, ['ano_letivo' => 2026, 'is_atual' => true]);

        $this->pei = Pei::create([
            'codigo' => 'PEI-2026-001',
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-10',
            'diagnostico' => 'TEA e Baixa Visão',
            'tempo_livre' => 'Programar e jogar xadrez',
            'pratica_esporte' => 'Natação',
            'caracteristicas_discente' => 'Focado e detalhista',
            'organizacao_e_prazos' => 'Muito pontual',
            'dificuldade_interacao_social' => 'Timidez inicial',
            'sugestoes_recursos_estrategias' => 'Alto contraste e tempo estendido',
            'status' => 'Ativo',
            'is_active' => true,
        ]);
    }

    public function test_dashboard_returns_dynamic_metrics(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('metrics.peis_count')
            ->has('metrics.alunos_count')
            ->has('metrics.turmas_count')
            ->has('metrics.ano_atual')
            ->has('metrics.trimestre_vigente')
            ->has('metrics.taxa_conclusao')
            ->has('minhasPendencias')
            ->has('peisRecentes')
            ->has('distribuicaoTurmas')
        );
    }

    public function test_dashboard_teacher_pending_alerts(): void
    {
        $professor = User::factory()->create([
            'cargo' => 'Professor',
            'email' => 'prof@sigepei.edu.br',
        ]);

        $disciplina = Disciplina::create([
            'codigo' => 'MAT-01',
            'nome' => 'Matemática',
        ]);

        $turmaDisc = \App\Models\TurmaDisciplina::create([
            'turma_id' => $this->turma->id,
            'disciplina_id' => $disciplina->id,
        ]);

        $turmaDisc->professores()->attach($professor->id);

        $response = $this->actingAs($professor)->get(route('dashboard', ['trimestre' => 2]));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('metrics.trimestre_selecionado', 2)
            ->where('minhasPendencias.0.tipo', 'disciplina')
            ->where('minhasPendencias.0.atribuicao', 'Matemática')
            ->where('minhasPendencias.0.aluno_nome', 'Lucas Gabriel Ferreira')
        );
    }

    public function test_aluno_show_returns_ficha_from_latest_pei(): void
    {
        $response = $this->actingAs($this->user)->get(route('alunos.show', $this->aluno->id));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Alunos/Show')
            ->where('aluno.ficha_pei.diagnostico', 'TEA e Baixa Visão')
            ->where('aluno.ficha_pei.tempo_livre', 'Programar e jogar xadrez')
            ->where('aluno.ficha_pei.pratica_esporte', 'Natação')
            ->where('aluno.ficha_pei.caracteristicas_discente', 'Focado e detalhista')
        );
    }

    public function test_pei_show_returns_ficha_aluno_and_ficha_pei(): void
    {
        $response = $this->actingAs($this->user)->get(route('peis.show', $this->pei->id));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Peis/Show')
            ->where('pei.aluno.data_nascimento', '15/05/2008')
            ->where('pei.idade_na_elaboracao', '17 anos')
            ->where('pei.ficha_aluno.tempo_livre', 'Programar e jogar xadrez')
            ->where('pei.ficha_aluno.pratica_esporte', 'Natação')
            ->where('pei.diagnostico', 'TEA e Baixa Visão')
        );
    }

    public function test_update_ficha_aluno(): void
    {
        $response = $this->actingAs($this->user)->post(route('peis.update-ficha-aluno', $this->pei->id), [
            'tempo_livre' => 'Gosta de ler livros de ficção',
            'pratica_esporte' => 'Ciclismo',
            'caracteristicas_discente' => 'Calmo e cooperativo',
            'organizacao_e_prazos' => 'Cumpre todos os prazos',
            'dificuldade_interacao_social' => 'Nenhuma dificuldade',
            'sugestoes_recursos_estrategias' => 'Textos ampliados',
        ]);

        $response->assertRedirect(route('peis.show', $this->pei->id));

        $this->pei->refresh();
        $this->assertEquals('Gosta de ler livros de ficção', $this->pei->tempo_livre);
        $this->assertEquals('Ciclismo', $this->pei->pratica_esporte);
    }

    public function test_update_ficha_pei(): void
    {
        $response = $this->actingAs($this->user)->post(route('peis.update-ficha-pei', $this->pei->id), [
            'diagnostico' => 'Transtorno do Espectro Autista',
            'responsaveis' => [
                ['tipo' => 'usuario', 'user_id' => $this->user->id],
                ['tipo' => 'externo', 'nome_externo' => 'Psicólogo Dr. André'],
            ],
            'habilidades' => ['Raciocínio lógico avançado'],
            'dificuldades' => ['Barulho excessivo'],
            'dificultadores_internos' => ['Salas com eco'],
            'facilitadores_internos' => ['Fones abafadores'],
            'dificultadores_externos' => ['Trânsito'],
            'facilitadores_externos' => ['Família presente'],
            'intervencoes' => [
                [
                    'demanda' => 'Adaptação de prova',
                    'encaminhamento_estrategia' => 'Aplicar em sala reservada',
                    'responsavel' => 'NAI e Docentes',
                ],
            ],
        ]);

        $response->assertRedirect(route('peis.show', $this->pei->id));

        $this->pei->refresh();
        $this->assertEquals('Transtorno do Espectro Autista', $this->pei->diagnostico);
        $this->assertCount(2, $this->pei->responsaveis);
        $this->assertCount(6, $this->pei->identificacoes);
        $this->assertCount(1, $this->pei->intervencoes);
    }

    public function test_pei_create_includes_ultimo_pei_for_auto_import(): void
    {
        $response = $this->actingAs($this->user)->get(route('peis.create'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Peis/Create')
            ->has('alunosDisponiveis.0.ultimo_pei')
            ->where('alunosDisponiveis.0.ultimo_pei.tempo_livre', 'Programar e jogar xadrez')
        );
    }
}
