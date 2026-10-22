<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Deficiencia;
use App\Models\Disciplina;
use App\Models\Pei;
use App\Models\PeiApoio;
use App\Models\Turma;
use App\Models\TurmaDisciplina;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeiFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminNai;
    protected User $professorAssigned;
    protected User $professorUnassigned;
    protected Aluno $aluno;
    protected Turma $turma;
    protected Disciplina $disciplina;
    protected TurmaDisciplina $turmaDisciplina;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminNai = User::factory()->create([
            'name' => 'Gestora NAI',
            'cargo' => 'NAI',
            'is_admin' => true,
        ]);

        $this->professorAssigned = User::factory()->create([
            'name' => 'Prof. Marcelo Titular',
            'cargo' => 'Professor',
            'is_admin' => false,
        ]);

        $this->professorUnassigned = User::factory()->create([
            'name' => 'Prof. Rodrigo Desvinculado',
            'cargo' => 'Professor',
            'is_admin' => false,
        ]);

        $this->turma = Turma::create([
            'nome_curso' => 'Técnico em Agropecuária 2026',
            'ano_letivo' => 2026,
            'is_active' => true,
        ]);

        $this->disciplina = Disciplina::create([
            'codigo' => 'AGRO-01',
            'nome' => 'Solos e Nutrição Mineral',
            'is_active' => true,
        ]);

        $this->turmaDisciplina = TurmaDisciplina::create([
            'turma_id' => $this->turma->id,
            'disciplina_id' => $this->disciplina->id,
        ]);

        $this->turmaDisciplina->professores()->attach($this->professorAssigned->id, [
            'is_substituto' => false,
        ]);

        $this->aluno = Aluno::create([
            'nome' => 'Larissa Manoela',
            'matricula' => '20269001',
            'data_nascimento' => '2008-06-20',
            'is_active' => true,
        ]);

        $this->aluno->turmas()->attach($this->turma->id, ['ano_letivo' => 2026, 'is_atual' => true]);
    }

    public function test_pei_index_renders_with_pei_list(): void
    {
        $pei = Pei::create([
            'codigo' => 'PEI-2026-001',
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-15',
            'status' => 'Ativo',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminNai)->get(route('peis.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Peis/Index')
            ->has('peis', 1)
            ->where('peis.0.codigo', 'PEI-2026-001')
            ->where('peis.0.aluno', 'Larissa Manoela')
        );
    }

    public function test_pei_can_be_stored_with_sequential_code_and_initial_tab_data(): void
    {
        $payload = [
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-20',
            'diagnostico' => 'TEA Grau 1 de Suporte',
            'tempo_livre' => 'Leitura e Robótica',
            'pratica_esporte' => 'Vôlei',
            'caracteristicas_discente' => 'Concentrado e criativo',
            'organizacao_e_prazos' => 'Utiliza agenda física',
            'dificuldade_interacao_social' => 'Dificuldade com trabalhos em grupos grandes',
            'sugestoes_recursos_estrategias' => 'Instruções visuais e pausas estratégicas',
            'responsaveis' => [
                ['user_id' => $this->adminNai->id],
                ['nome_externo' => 'Fonoaudióloga Dra. Carla'],
            ],
            'habilidades' => ['Raciocínio lógico', 'Memória visual'],
            'dificuldades' => ['Processamento auditivo em ambientes ruidosos'],
            'dificultadores_internos' => ['Eco na sala de aula'],
            'facilitadores_internos' => ['Abafador de ruídos'],
            'dificultadores_externos' => ['Mudança repentina de itinerário do ônibus'],
            'facilitadores_externos' => ['Apoio familiar constante'],
            'intervencoes' => [
                [
                    'demanda' => 'Adaptação de avaliações escritas',
                    'encaminhamento_estrategia' => 'Uso de fonte ampliada e enunciados diretos',
                    'responsavel' => 'Docentes e NAI',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminNai)->post(route('peis.store'), $payload);

        $pei = Pei::where('aluno_id', $this->aluno->id)->where('ano_letivo', 2026)->first();
        $this->assertNotNull($pei);
        $this->assertEquals('PEI-2026-001', $pei->codigo);

        $response->assertRedirect(route('peis.show', $pei->id));

        $this->assertCount(2, $pei->responsaveis);
        $this->assertCount(7, $pei->identificacoes);
        $this->assertCount(1, $pei->intervencoes);

        // teste de geração sequencial no mesmo ano letivo
        $outroAluno = Aluno::create([
            'nome' => 'Bruno Castro',
            'matricula' => '20269002',
            'data_nascimento' => '2008-01-01',
        ]);
        $payload2 = $payload;
        $payload2['aluno_id'] = $outroAluno->id;

        $this->actingAs($this->adminNai)->post(route('peis.store'), $payload2);
        $pei2 = Pei::where('aluno_id', $outroAluno->id)->where('ano_letivo', 2026)->first();
        $this->assertNotNull($pei2);
        $this->assertEquals('PEI-2026-002', $pei2->codigo);
    }

    public function test_pei_update_nai_actions_for_trimester(): void
    {
        $pei = Pei::create([
            'codigo' => 'PEI-2026-003',
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-15',
            'is_active' => true,
        ]);

        $payloadNai = [
            'trimestre' => 1,
            'responsaveis' => [
                ['user_id' => $this->adminNai->id],
            ],
            'atividades' => [
                [
                    'objetivos' => 'Acompanhar ambientação e rotina escolar',
                    'recursos_acoes' => 'Entrevistas semanais e mediação pedagógica',
                    'tempo' => 'Fevereiro a Abril',
                    'avaliacao_resultados' => 'Aluno totalmente integrado à turma',
                    'sugestoes_aproveitamento' => 'Manter reuniões quinzenais',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminNai)->post(route('peis.update-nai', $pei->id), $payloadNai);

        $response->assertRedirect(route('peis.show', $pei->id));

        $this->assertDatabaseHas('pei_nai_atividades', [
            'pei_id' => $pei->id,
            'trimestre' => 1,
            'objetivos' => 'Acompanhar ambientação e rotina escolar',
            'avaliacao_resultados' => 'Aluno totalmente integrado à turma',
        ]);

        $this->assertDatabaseHas('pei_responsaveis', [
            'pei_id' => $pei->id,
            'user_id' => $this->adminNai->id,
            'secao' => 'nai_1',
        ]);
    }

    public function test_pei_equipe_apoio_complete_lifecycle(): void
    {
        $pei = Pei::create([
            'codigo' => 'PEI-2026-004',
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-15',
            'is_active' => true,
        ]);

        // 1. criação de equipe de apoio (aee)
        $resStore = $this->actingAs($this->adminNai)->post(route('peis.store-apoio', $pei->id), [
            'nome' => 'Atendimento Educacional Especializado (AEE)',
        ]);
        $resStore->assertRedirect(route('peis.show', $pei->id));

        $apoio = PeiApoio::where('pei_id', $pei->id)->first();
        $this->assertNotNull($apoio);
        $this->assertEquals('Atendimento Educacional Especializado (AEE)', $apoio->nome);

        // 2. atualização das ações do 1º trimestre da equipe de apoio
        $payloadTrimestre = [
            'trimestre' => 1,
            'responsaveis' => [
                ['nome_externo' => 'Especialista em AEE Profa. Laura'],
            ],
            'atividades' => [
                [
                    'objetivos' => 'Treinamento de tecnologias assistivas',
                    'recursos_acoes' => 'Softwares leitores de tela e ampliação',
                    'tempo' => '2h semanais no contraturno',
                    'avaliacao_resultados' => 'Domínio satisfatório das ferramentas',
                    'sugestoes_aproveitamento' => 'Integrar com o laboratório de informática',
                ],
            ],
        ];

        $resUpdate = $this->actingAs($this->adminNai)->post(route('peis.update-apoio-trimestre', [
            'pei' => $pei->id,
            'apoio' => $apoio->id,
        ]), $payloadTrimestre);

        $resUpdate->assertRedirect(route('peis.show', $pei->id));

        $this->assertDatabaseHas('pei_apoio_atividades', [
            'pei_apoio_id' => $apoio->id,
            'trimestre' => 1,
            'objetivos' => 'Treinamento de tecnologias assistivas',
        ]);

        // 3. exclusão da equipe de apoio
        $resDelete = $this->actingAs($this->adminNai)->delete(route('peis.destroy-apoio', [
            'pei' => $pei->id,
            'apoio' => $apoio->id,
        ]));

        $resDelete->assertRedirect(route('peis.show', $pei->id));
        $this->assertDatabaseMissing('pei_apoios', ['id' => $apoio->id]);
        $this->assertDatabaseMissing('pei_apoio_atividades', ['pei_apoio_id' => $apoio->id]);
    }

    public function test_teacher_assigned_to_turma_disciplina_can_update_planejamento(): void
    {
        $pei = Pei::create([
            'codigo' => 'PEI-2026-005',
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-15',
            'is_active' => true,
        ]);

        $payload = [
            'trimestre' => 1,
            'grau_autonomia' => ['Autonomia com apoio', 'Compreende com mediação'],
            'especifique_outros' => 'Necessita de exemplos práticos no campo',
            'planejamentos' => [
                [
                    'objetivos' => 'Identificar horizontes do solo',
                    'conteudos' => 'Morfologia e gênese dos solos',
                    'metodologias_recursos' => 'Aulas práticas no campo experimental com lupas',
                    'tempo' => '3 semanas',
                    'avaliacao_resultados' => 'Diferenciou as camadas com precisão',
                    'sugestoes_aproveitamento' => 'Passar para fertilidade do solo',
                ],
            ],
            'avaliacoes' => [
                [
                    'tipo' => 'Avaliação prática de campo',
                    'adotado' => true,
                    'valor_1' => '10.0',
                    'nota_1' => '9.5',
                ],
            ],
        ];

        $response = $this->actingAs($this->professorAssigned)->post(route('peis.update-disciplina-trimestre', [
            'pei' => $pei->id,
            'disciplina' => $this->disciplina->id,
        ]), $payload);

        $response->assertRedirect(route('peis.show', $pei->id));

        $this->assertDatabaseHas('pei_disciplinas', [
            'pei_id' => $pei->id,
            'disciplina_id' => $this->disciplina->id,
            'trimestre' => 1,
            'especifique_outros' => 'Necessita de exemplos práticos no campo',
        ]);

        $this->assertDatabaseHas('pei_disciplina_planejamentos', [
            'objetivos' => 'Identificar horizontes do solo',
            'tempo' => '3 semanas',
        ]);

        $this->assertDatabaseHas('pei_disciplina_avaliacoes', [
            'tipo' => 'Avaliação prática de campo',
            'adotado' => true,
            'nota_1' => '9.5',
        ]);
    }

    public function test_teacher_not_assigned_to_turma_disciplina_is_forbidden(): void
    {
        $pei = Pei::create([
            'codigo' => 'PEI-2026-006',
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-15',
            'is_active' => true,
        ]);

        $payload = [
            'trimestre' => 1,
            'grau_autonomia' => [],
            'planejamentos' => [
                [
                    'objetivos' => 'Teste não autorizado',
                    'conteudos' => 'Não autorizado',
                    'metodologias_recursos' => 'Sem acesso',
                    'tempo' => '1 semana',
                ],
            ],
            'avaliacoes' => [],
        ];

        // docente não vinculado recebe código 403 (proibido)
        $response = $this->actingAs($this->professorUnassigned)->post(route('peis.update-disciplina-trimestre', [
            'pei' => $pei->id,
            'disciplina' => $this->disciplina->id,
        ]), $payload);

        $response->assertStatus(403);
    }

    public function test_admin_and_nai_can_update_planejamento_regardless_of_assignment(): void
    {
        $pei = Pei::create([
            'codigo' => 'PEI-2026-007',
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-15',
            'is_active' => true,
        ]);

        $payload = [
            'trimestre' => 1,
            'grau_autonomia' => ['Autônomo'],
            'planejamentos' => [
                [
                    'objetivos' => 'Revisão Pedagógica pelo NAI',
                    'conteudos' => 'Metodologia adaptada',
                    'metodologias_recursos' => 'Recurso multissensorial',
                    'tempo' => '2 semanas',
                ],
            ],
            'avaliacoes' => [],
        ];

        $response = $this->actingAs($this->adminNai)->post(route('peis.update-disciplina-trimestre', [
            'pei' => $pei->id,
            'disciplina' => $this->disciplina->id,
        ]), $payload);

        $response->assertRedirect(route('peis.show', $pei->id));

        $this->assertDatabaseHas('pei_disciplina_planejamentos', [
            'objetivos' => 'Revisão Pedagógica pelo NAI',
        ]);
    }

    public function test_pei_active_status_can_be_toggled(): void
    {
        $pei = Pei::create([
            'codigo' => 'PEI-2026-008',
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-15',
            'is_active' => true,
        ]);

        // inativação do pei
        $resInativar = $this->actingAs($this->adminNai)->delete(route('peis.destroy', $pei->id));
        $resInativar->assertRedirect(route('peis.index'));
        $this->assertFalse((bool) $pei->fresh()->is_active);

        // reativação do pei
        $resReativar = $this->actingAs($this->adminNai)->delete(route('peis.destroy', $pei->id));
        $resReativar->assertRedirect(route('peis.index'));
        $this->assertTrue((bool) $pei->fresh()->is_active);
    }

    public function test_teacher_can_save_structured_options_in_pei_disciplina_planejamento(): void
    {
        $pei = Pei::create([
            'codigo' => 'PEI-2026-009',
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-15',
            'is_active' => true,
        ]);

        $payload = [
            'trimestre' => 1,
            'grau_autonomia' => ['autonomo'],
            'especifique_outros' => null,
            'planejamentos' => [
                [
                    'objetivos' => 'Compreender ciclos biológicos',
                    'conteudos' => 'Ecossistemas e cadeias alimentares',
                    'estrategias_ensino' => ['Aula expositiva', 'Jogos e dinâmicas'],
                    'estrategias_ensino_outros' => 'Mapas mentais interativos',
                    'estrategias_avaliacao' => ['Questões apresentadas de forma objetiva e direta', 'Prova oral'],
                    'estrategias_avaliacao_outros' => 'Gravação de áudio explicativo',
                    'tempo_opcao' => 'Outro',
                    'tempo_outro' => '3 semanas intensivas',
                    'avaliacao_desempenho' => [
                        'Atingiu o objetivo proposto com autonomia.',
                        'Desenvolveu maior autonomia na realização das atividades.',
                    ],
                    'avaliacao_desempenho_outros' => 'Excelente participação em grupo',
                    'sugestoes_aproveitamento' => 'Manter estímulos visuais',
                ],
            ],
            'avaliacoes' => [
                [
                    'tipo' => 'prova_escrita',
                    'adotado' => true,
                    'valor_1' => '10.0',
                    'nota_1' => '9.0',
                ],
            ],
        ];

        $response = $this->actingAs($this->professorAssigned)->post(route('peis.update-disciplina-trimestre', [
            'pei' => $pei->id,
            'disciplina' => $this->disciplina->id,
        ]), $payload);

        $response->assertRedirect(route('peis.show', $pei->id));

        $this->assertDatabaseHas('pei_disciplina_planejamentos', [
            'objetivos' => 'Compreender ciclos biológicos',
            'conteudos' => 'Ecossistemas e cadeias alimentares',
            'estrategias_ensino_outros' => 'Mapas mentais interativos',
            'estrategias_avaliacao_outros' => 'Gravação de áudio explicativo',
            'tempo_opcao' => 'Outro',
            'tempo_outro' => '3 semanas intensivas',
            'tempo' => '3 semanas intensivas',
            'avaliacao_desempenho_outros' => 'Excelente participação em grupo',
            'sugestoes_aproveitamento' => 'Manter estímulos visuais',
        ]);

        $plan = \App\Models\PeiDisciplinaPlanejamento::where('objetivos', 'Compreender ciclos biológicos')->first();
        $this->assertNotNull($plan);
        $this->assertContains('Aula expositiva', $plan->estrategias_ensino);
        $this->assertContains('Jogos e dinâmicas', $plan->estrategias_ensino);
        $this->assertContains('Questões apresentadas de forma objetiva e direta', $plan->estrategias_avaliacao);
        $this->assertContains('Prova oral', $plan->estrategias_avaliacao);
        $this->assertContains('Atingiu o objetivo proposto com autonomia.', $plan->avaliacao_desempenho);

        // verifica se os campos de compatibilidade textual foram devidamente sintetizados
        $this->assertStringContainsString('Estratégias de ensino:', $plan->metodologias_recursos);
        $this->assertStringContainsString('Aula expositiva', $plan->metodologias_recursos);
        $this->assertStringContainsString('Outras estratégias de ensino: Mapas mentais interativos', $plan->metodologias_recursos);
        $this->assertStringContainsString('Estratégias e condições de avaliação:', $plan->metodologias_recursos);
        $this->assertStringContainsString('Atingiu o objetivo proposto com autonomia.', $plan->avaliacao_resultados);
        $this->assertStringContainsString('Excelente participação em grupo', $plan->avaliacao_resultados);
    }

    public function test_dashboard_provides_dados_edicao_and_supports_quick_edit_redirect_to(): void
    {
        $pei = Pei::create([
            'codigo' => 'PEI-2026-010',
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-15',
            'is_active' => true,
        ]);

        // Acessa o dashboard como o professor atribuído
        $res = $this->actingAs($this->professorAssigned)->get(route('dashboard', ['trimestre' => 1]));
        $res->assertOk();

        $minhasPendencias = $res->viewData('page')['props']['minhasPendencias'];
        $pendencia = collect($minhasPendencias)->firstWhere('pei_id', $pei->id);

        $this->assertNotNull($pendencia);
        $this->assertEquals('disciplina', $pendencia['tipo']);
        $this->assertArrayHasKey('dados_edicao', $pendencia);
        $this->assertArrayHasKey('deficiencias', $pendencia);
        $this->assertEquals($this->disciplina->codigo, $pendencia['disciplina_codigo']);

        // Submete a edição rápida diretamente do dashboard passando redirect_to
        $payload = [
            'trimestre' => 1,
            'planejamentos' => [
                [
                    'objetivos' => 'Preenchimento rápido direto pelo Dashboard',
                    'conteudos' => 'Adaptações e estratégias',
                    'estrategias_ensino' => ['Aula expositiva'],
                    'tempo_opcao' => 'Todo o trimestre',
                ],
            ],
            'avaliacoes' => [
                [
                    'tipo' => 'prova_escrita',
                    'adotado' => true,
                    'valor_1' => '10.0',
                    'nota_1' => '8.5',
                ],
            ],
            'redirect_to' => route('dashboard', ['trimestre' => 1]),
        ];

        $response = $this->actingAs($this->professorAssigned)->post(route('peis.update-disciplina-trimestre', [
            'pei' => $pei->id,
            'disciplina' => $this->disciplina->id,
        ]), $payload);

        // Deve redirecionar para o dashboard conforme solicitado
        $response->assertRedirect(route('dashboard', ['trimestre' => 1]));
        $response->assertSessionHas('success');

        // Confirma que a pendência foi sanada no banco
        $this->assertDatabaseHas('pei_disciplina_planejamentos', [
            'objetivos' => 'Preenchimento rápido direto pelo Dashboard',
            'tempo' => 'Todo o trimestre',
        ]);
    }
}
