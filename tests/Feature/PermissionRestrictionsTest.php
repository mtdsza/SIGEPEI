<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\AuditLog;
use App\Models\Deficiencia;
use App\Models\Disciplina;
use App\Models\Pei;
use App\Models\PeiApoio;
use App\Models\PeiApoioResponsavel;
use App\Models\Turma;
use App\Models\TurmaDisciplina;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionRestrictionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $nai;
    private User $professor;
    private User $outroProfessor;
    private Turma $turma;
    private Disciplina $disciplina;
    private Aluno $aluno;
    private Pei $pei;
    private PeiApoio $apoio;

    protected function setUp(): void
    {
        parent::setUp();

        // configuração inicial de usuários com diferentes níveis de permissão
        $this->admin = User::factory()->create([
            'name' => 'Administrador do Sistema',
            'cargo' => 'Outros',
            'is_admin' => true,
            'is_active' => true,
        ]);

        $this->nai = User::factory()->create([
            'name' => 'Servidor do NAI',
            'cargo' => 'NAI',
            'is_admin' => false,
            'is_active' => true,
        ]);

        $this->professor = User::factory()->create([
            'name' => 'Professor Titular',
            'cargo' => 'Professor',
            'is_admin' => false,
            'is_active' => true,
        ]);

        $this->outroProfessor = User::factory()->create([
            'name' => 'Outro Professor',
            'cargo' => 'Professor',
            'is_admin' => false,
            'is_active' => true,
        ]);

        // estrutura básica de turma, disciplina e aluno para os testes
        $this->disciplina = Disciplina::create([
            'codigo' => 'MAT-01',
            'nome' => 'Matemática I',
            'is_active' => true,
        ]);

        $this->turma = Turma::create([
            'nome_curso' => 'Informática Integrado',
            'ano_letivo' => 2026,
            'is_active' => true,
        ]);

        $turmaDisciplina = TurmaDisciplina::create([
            'turma_id' => $this->turma->id,
            'disciplina_id' => $this->disciplina->id,
        ]);
        $turmaDisciplina->professores()->attach($this->professor->id, ['is_substituto' => false]);

        $this->aluno = Aluno::create([
            'nome' => 'Estudante Teste',
            'matricula' => '20261001',
            'data_nascimento' => '2010-05-15',
            'is_active' => true,
        ]);

        $this->pei = Pei::create([
            'codigo' => 'PEI-2026-0001',
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-10',
            'is_active' => true,
        ]);

        $this->apoio = PeiApoio::create([
            'pei_id' => $this->pei->id,
            'nome' => 'Equipe de Psicopedagogia',
            'ordem' => 1,
        ]);
    }

    /**
     * verifica que professores não conseguem acessar rotas administrativas restritas.
     */
    public function test_professor_cannot_access_administrative_areas(): void
    {
        $this->actingAs($this->professor);

        // deficiências
        $this->get(route('deficiencias.index'))->assertStatus(403);
        $this->get(route('deficiencias.create'))->assertStatus(403);
        $this->post(route('deficiencias.store'), ['codigo' => 'D01', 'nome' => 'Deficiência'])->assertStatus(403);

        // disciplinas
        $this->get(route('disciplinas.index'))->assertStatus(403);
        $this->get(route('disciplinas.create'))->assertStatus(403);
        $this->post(route('disciplinas.store'), ['codigo' => 'FIS', 'nome' => 'Física'])->assertStatus(403);

        // calendário e auditoria
        $this->get(route('calendario.index'))->assertStatus(403);
        $this->get(route('auditoria.index'))->assertStatus(403);

        // gestão de usuários
        $this->get(route('usuarios.index'))->assertStatus(403);
    }

    /**
     * verifica que professores não conseguem criar, editar ou inativar alunos e turmas.
     */
    public function test_professor_cannot_create_or_modify_alunos_and_turmas(): void
    {
        $this->actingAs($this->professor);

        // criação e edição de alunos
        $this->get(route('alunos.create'))->assertStatus(403);
        $this->post(route('alunos.store'), [
            'nome' => 'Novo Aluno',
            'matricula' => '20269999',
            'data_nascimento' => '2010-01-01',
        ])->assertStatus(403);
        $this->get(route('alunos.edit', $this->aluno->id))->assertStatus(403);
        $this->put(route('alunos.update', $this->aluno->id), [
            'nome' => 'Aluno Editado',
            'matricula' => $this->aluno->matricula,
            'data_nascimento' => '2010-05-15',
        ])->assertStatus(403);
        $this->delete(route('alunos.destroy', $this->aluno->id))->assertStatus(403);
        $this->post(route('alunos.associar-turma', $this->aluno->id), [
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
        ])->assertStatus(403);

        // criação e edição de turmas
        $this->get(route('turmas.create'))->assertStatus(403);
        $this->post(route('turmas.store'), [
            'nome_curso' => 'Novo Curso',
            'ano_letivo' => 2026,
        ])->assertStatus(403);
        $this->get(route('turmas.edit', $this->turma->id))->assertStatus(403);
        $this->put(route('turmas.update', $this->turma->id), [
            'nome_curso' => 'Curso Atualizado',
            'ano_letivo' => 2026,
        ])->assertStatus(403);
        $this->delete(route('turmas.destroy', $this->turma->id))->assertStatus(403);
    }

    /**
     * verifica que professores não conseguem criar peis nem editar seções gerais do plano.
     */
    public function test_professor_cannot_create_peis_or_edit_general_sections(): void
    {
        $this->actingAs($this->professor);

        // criação e exclusão do pei
        $this->get(route('peis.create'))->assertStatus(403);
        $this->post(route('peis.store'), [
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-15',
        ])->assertStatus(403);
        $this->delete(route('peis.destroy', $this->pei->id))->assertStatus(403);

        // edição das seções estruturais do pei
        $this->post(route('peis.update-ficha-aluno', $this->pei->id), [
            'historico_escolar' => 'Texto qualquer',
        ])->assertStatus(403);

        $this->post(route('peis.update-ficha-pei', $this->pei->id), [
            'diagnostico' => 'Novo diagnóstico',
        ])->assertStatus(403);

        $this->post(route('peis.update-nai', $this->pei->id), [
            'trimestre' => 1,
            'atividades' => [],
        ])->assertStatus(403);

        // gestão de abas de equipes de apoio
        $this->post(route('peis.store-apoio', $this->pei->id), [
            'nome' => 'Novo Apoio',
        ])->assertStatus(403);
        $this->put(route('peis.update-apoio', [$this->pei->id, $this->apoio->id]), [
            'nome' => 'Apoio Renomeado',
        ])->assertStatus(403);
        $this->delete(route('peis.destroy-apoio', [$this->pei->id, $this->apoio->id]))->assertStatus(403);
    }

    /**
     * verifica que o professor consegue preencher apenas a disciplina onde está alocado como docente.
     */
    public function test_professor_can_only_update_assigned_discipline_trimestre(): void
    {
        // professor alocado na disciplina de matemática
        $this->actingAs($this->professor);

        $payload = [
            'trimestre' => 1,
            'atividades' => [
                [
                    'objetivos' => 'Compreensão de funções',
                    'recursos_acoes' => 'Exercícios adaptados',
                    'tempo' => '4 semanas',
                    'avaliacao_resultados' => 'Bom progresso',
                    'sugestoes_aproveitamento' => 'Manter ritmo',
                ],
            ],
        ];

        $response = $this->post(route('peis.update-disciplina-trimestre', [$this->pei->id, $this->disciplina->id]), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('peis.show', $this->pei->id));

        // outro professor não alocado na disciplina tenta preencher
        $this->actingAs($this->outroProfessor);
        $this->post(route('peis.update-disciplina-trimestre', [$this->pei->id, $this->disciplina->id]), $payload)
            ->assertStatus(403);
    }

    /**
     * verifica que o professor só pode editar ações de apoio caso esteja indicado como responsável no trimestre.
     */
    public function test_professor_can_only_update_apoio_when_designated_responsible(): void
    {
        $payload = [
            'trimestre' => 1,
            'responsaveis' => [
                ['tipo' => 'usuario', 'user_id' => $this->professor->id, 'nome_externo' => ''],
            ],
            'atividades' => [
                [
                    'objetivos' => 'Atendimento psicológico',
                    'recursos_acoes' => 'Escuta ativa semanal',
                    'tempo' => 'Bimestral',
                    'avaliacao_resultados' => 'Evoluindo',
                    'sugestoes_aproveitamento' => 'Continuar acompanhamento',
                ],
            ],
        ];

        // outro professor que não é responsável tenta editar e recebe 403
        $this->actingAs($this->outroProfessor);
        $this->post(route('peis.update-apoio-trimestre', [$this->pei->id, $this->apoio->id]), $payload)
            ->assertStatus(403);

        // equipe nai cadastra o professor como responsável no primeiro trimestre
        PeiApoioResponsavel::create([
            'pei_apoio_id' => $this->apoio->id,
            'trimestre' => 1,
            'tipo' => 'usuario',
            'user_id' => $this->professor->id,
            'ordem' => 0,
        ]);

        // agora o professor indicado consegue registrar as ações do trimestre
        $this->actingAs($this->professor);
        $response = $this->post(route('peis.update-apoio-trimestre', [$this->pei->id, $this->apoio->id]), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('peis.show', $this->pei->id));
    }

    /**
     * verifica que usuários com cargo NAI possuem acesso completo à gestão pedagógica e cadastral.
     */
    public function test_nai_user_has_full_management_access(): void
    {
        $this->actingAs($this->nai);

        // acesso às páginas administrativas
        $this->get(route('deficiencias.index'))->assertStatus(200);
        $this->get(route('disciplinas.index'))->assertStatus(200);
        $this->get(route('calendario.index'))->assertStatus(200);
        $this->get(route('auditoria.index'))->assertStatus(200);
        $this->get(route('peis.create'))->assertStatus(200);
        $this->get(route('alunos.create'))->assertStatus(200);
        $this->get(route('turmas.create'))->assertStatus(200);

        // capacidade de cadastrar nova deficiência
        $this->post(route('deficiencias.store'), [
            'codigo' => 'DEF-02',
            'nome' => 'Baixa Visão',
        ])->assertRedirect(route('deficiencias.index'));

        // capacidade de cadastrar nova disciplina
        $this->post(route('disciplinas.store'), [
            'codigo' => 'BIO',
            'nome' => 'Biologia',
        ])->assertRedirect(route('disciplinas.index'));

        // capacidade de preencher qualquer disciplina de pei
        $this->post(route('peis.update-disciplina-trimestre', [$this->pei->id, $this->disciplina->id]), [
            'trimestre' => 1,
            'atividades' => [],
        ])->assertRedirect(route('peis.show', $this->pei->id));

        // usuários do nai não gerenciam usuários do sistema (exclusivo para administradores)
        $this->get(route('usuarios.index'))->assertStatus(403);
    }

    /**
     * verifica que usuários administradores possuem acesso irrestrito a todas as áreas e gestão de usuários.
     */
    public function test_admin_user_has_unrestricted_access(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('usuarios.index'))->assertStatus(200);
        $this->get(route('usuarios.create'))->assertStatus(200);
        $this->get(route('deficiencias.index'))->assertStatus(200);
        $this->get(route('disciplinas.index'))->assertStatus(200);
        $this->get(route('calendario.index'))->assertStatus(200);
        $this->get(route('auditoria.index'))->assertStatus(200);
        $this->get(route('peis.create'))->assertStatus(200);
        $this->get(route('alunos.create'))->assertStatus(200);
        $this->get(route('turmas.create'))->assertStatus(200);
    }

    /**
     * verifica que profissionais com cargo 'Outros' (não administradores) só editam partes onde são responsáveis.
     */
    public function test_user_with_cargo_outros_restricted_to_designated_responsibilities(): void
    {
        $outroProfissional = User::factory()->create([
            'name' => 'Psicólogo Escolar',
            'cargo' => 'Outros',
            'is_admin' => false,
            'is_active' => true,
        ]);

        $this->actingAs($outroProfissional);

        // não tem acesso a rotas administrativas gerais
        $this->get(route('deficiencias.index'))->assertStatus(403);
        $this->get(route('disciplinas.index'))->assertStatus(403);
        $this->get(route('calendario.index'))->assertStatus(403);
        $this->get(route('auditoria.index'))->assertStatus(403);
        $this->get(route('usuarios.index'))->assertStatus(403);
        $this->get(route('peis.create'))->assertStatus(403);
        $this->get(route('alunos.create'))->assertStatus(403);
        $this->get(route('turmas.create'))->assertStatus(403);

        // não pode editar ficha geral do aluno, do pei ou do nai
        $this->post(route('peis.update-ficha-aluno', $this->pei->id), [])->assertStatus(403);
        $this->post(route('peis.update-ficha-pei', $this->pei->id), [])->assertStatus(403);
        $this->post(route('peis.update-nai', $this->pei->id), ['trimestre' => 1])->assertStatus(403);

        // não pode editar disciplina em que não é docente
        $this->post(route('peis.update-disciplina-trimestre', [$this->pei->id, $this->disciplina->id]), [
            'trimestre' => 1,
            'atividades' => [],
        ])->assertStatus(403);

        // não pode editar equipe de apoio sem designação de responsabilidade
        $payloadApoio = [
            'trimestre' => 1,
            'responsaveis' => [
                ['tipo' => 'usuario', 'user_id' => $outroProfissional->id, 'nome_externo' => ''],
            ],
            'atividades' => [
                [
                    'objetivos' => 'Atendimento',
                    'recursos_acoes' => 'Escuta',
                    'tempo' => '1h',
                ],
            ],
        ];
        $this->post(route('peis.update-apoio-trimestre', [$this->pei->id, $this->apoio->id]), $payloadApoio)->assertStatus(403);

        // ao ser designado como responsável na equipe de apoio, consegue salvar as ações do trimestre
        PeiApoioResponsavel::create([
            'pei_apoio_id' => $this->apoio->id,
            'trimestre' => 1,
            'tipo' => 'usuario',
            'user_id' => $outroProfissional->id,
            'ordem' => 0,
        ]);

        $response = $this->post(route('peis.update-apoio-trimestre', [$this->pei->id, $this->apoio->id]), $payloadApoio);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('peis.show', $this->pei->id));
    }

    /**
     * verifica que o acompanhamento trimestral no painel exibe apenas peis com responsabilidade direta do usuário.
     */
    public function test_dashboard_acompanhamento_trimestral_shows_only_peis_where_user_is_responsible(): void
    {
        // cria um segundo pei onde nenhum dos usuários acima é responsável inicialmente
        $outroAluno = Aluno::create([
            'nome' => 'Outro Estudante',
            'matricula' => '20261002',
            'data_nascimento' => '2011-03-20',
            'is_active' => true,
        ]);

        $outraTurma = Turma::create([
            'nome_curso' => 'Edificações Integrado',
            'ano_letivo' => 2026,
            'is_active' => true,
        ]);

        $segundoPei = Pei::create([
            'codigo' => 'PEI-2026-0002',
            'aluno_id' => $outroAluno->id,
            'turma_id' => $outraTurma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-20',
            'is_active' => true,
        ]);

        // o professor leciona apenas na turma do primeiro pei
        $this->actingAs($this->professor);
        $res = $this->get(route('dashboard'));
        $res->assertStatus(200);
        $peisProfessor = collect($res->original->getData()['page']['props']['peisRecentes']);
        $this->assertTrue($peisProfessor->contains('codigo', $this->pei->codigo));
        $this->assertFalse($peisProfessor->contains('codigo', $segundoPei->codigo));

        // o outro professor não possui vínculo com nenhum dos dois peis inicialmente
        $this->actingAs($this->outroProfessor);
        $resOutro = $this->get(route('dashboard'));
        $peisOutro = collect($resOutro->original->getData()['page']['props']['peisRecentes']);
        $this->assertEmpty($peisOutro);

        // ao associar o outro professor como responsável na ficha inicial do segundo pei
        $segundoPei->responsaveis()->create([
            'user_id' => $this->outroProfessor->id,
            'secao' => 'ficha_inicial',
        ]);

        $resOutroAtualizado = $this->get(route('dashboard'));
        $peisOutroAtualizado = collect($resOutroAtualizado->original->getData()['page']['props']['peisRecentes']);
        $this->assertCount(1, $peisOutroAtualizado);
        $this->assertEquals($segundoPei->codigo, $peisOutroAtualizado->first()['codigo']);
    }

    /**
     * verifica que o status trimestral no painel marca como concluído se a parte do usuário logado foi preenchida.
     */
    public function test_dashboard_trimester_status_shows_complete_if_logged_in_users_part_is_filled(): void
    {
        // adiciona uma segunda disciplina (história) na mesma turma vinculada ao outro professor
        $disciplinaHistoria = Disciplina::create([
            'codigo' => 'HIS-01',
            'nome' => 'História I',
            'is_active' => true,
        ]);

        $tdHistoria = TurmaDisciplina::create([
            'turma_id' => $this->turma->id,
            'disciplina_id' => $disciplinaHistoria->id,
        ]);
        $tdHistoria->professores()->attach($this->outroProfessor->id, ['is_substituto' => false]);

        // o professor de matemática preenche seu planejamento e avaliação do 1º trimestre
        $this->actingAs($this->professor);
        $this->post(route('peis.update-disciplina-trimestre', [$this->pei->id, $this->disciplina->id]), [
            'trimestre' => 1,
            'planejamentos' => [
                [
                    'objetivos' => 'Objetivos de Matemática',
                    'conteudos' => 'Funções e Álgebra',
                    'metodologias_recursos' => 'Aulas expositivas e práticas',
                    'tempo' => '4 semanas',
                ],
            ],
            'avaliacoes' => [
                [
                    'tipo' => 'diagnostica',
                    'adotado' => true,
                    'valor_1' => '10',
                ],
            ],
        ]);

        // para o professor de matemática, o 1º trimestre do pei deve constar como completo
        $resProfessor = $this->get(route('dashboard'));
        $peisProfessor = collect($resProfessor->original->getData()['page']['props']['peisRecentes']);
        $peiProfData = $peisProfessor->firstWhere('codigo', $this->pei->codigo);
        $this->assertEquals('completo', $peiProfData['status_trimestres'][1]['status']);

        // para o professor de história (que ainda não preencheu), o 1º trimestre deve constar como pendente
        $this->actingAs($this->outroProfessor);
        $resOutro = $this->get(route('dashboard'));
        $peisOutro = collect($resOutro->original->getData()['page']['props']['peisRecentes']);
        $peiOutroData = $peisOutro->firstWhere('codigo', $this->pei->codigo);
        $this->assertEquals('pendente', $peiOutroData['status_trimestres'][1]['status']);
    }
}
