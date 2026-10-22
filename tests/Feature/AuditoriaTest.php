<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\AuditLog;
use App\Models\Deficiencia;
use App\Models\Pei;
use App\Models\Turma;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $professor;
    protected User $nai;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Teste',
            'email' => 'admin@teste.local',
            'cargo' => 'NAI',
            'is_admin' => true,
            'is_active' => true,
        ]);

        $this->nai = User::factory()->create([
            'name' => 'Pedagoga NAI',
            'email' => 'nai@teste.local',
            'cargo' => 'NAI',
            'is_admin' => false,
            'is_active' => true,
        ]);

        $this->professor = User::factory()->create([
            'name' => 'Professor Teste',
            'email' => 'prof@teste.local',
            'cargo' => 'Professor',
            'is_admin' => false,
            'is_active' => true,
        ]);
    }

    public function test_professor_sem_privilegios_nao_pode_acessar_auditoria(): void
    {
        $response = $this->actingAs($this->professor)->get(route('auditoria.index'));
        $response->assertStatus(403);
    }

    public function test_admin_e_nai_podem_acessar_auditoria(): void
    {
        $resAdmin = $this->actingAs($this->admin)->get(route('auditoria.index'));
        $resAdmin->assertStatus(200);

        $resNai = $this->actingAs($this->nai)->get(route('auditoria.index'));
        $resNai->assertStatus(200);
    }

    public function test_audit_service_registra_log_automaticamente(): void
    {
        $this->actingAs($this->admin);

        AuditService::log(
            'PEIs',
            'Criação',
            'Criou o PEI PEI-2026-TESTE',
            null,
            null,
            ['codigo' => 'PEI-2026-TESTE']
        );

        $this->assertDatabaseHas('audit_logs', [
            'modulo' => 'PEIs',
            'acao' => 'Criação',
            'descricao' => 'Criou o PEI PEI-2026-TESTE',
            'user_name' => 'Admin Teste',
        ]);
    }

    public function test_criacao_de_aluno_gera_log_de_auditoria(): void
    {
        $this->actingAs($this->admin);

        $def = Deficiencia::create(['codigo' => 'F84.0', 'nome' => 'TEA', 'is_active' => true]);

        $this->post(route('alunos.store'), [
            'nome' => 'Aluno Auditado',
            'matricula' => '20269999',
            'data_nascimento' => '2008-01-01',
            'deficiencias' => [$def->id],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'modulo' => 'Alunos',
            'acao' => 'Criação',
            'user_name' => 'Admin Teste',
        ]);
    }

    public function test_visualizacao_de_detalhes_do_log_de_auditoria(): void
    {
        $this->actingAs($this->admin);

        $log = AuditLog::create([
            'user_id' => $this->admin->id,
            'user_name' => $this->admin->name,
            'user_email' => $this->admin->email,
            'user_cargo' => $this->admin->cargo,
            'modulo' => 'PEIs',
            'acao' => 'Atualização',
            'descricao' => 'Atualizou ficha inicial',
            'dados_anteriores' => ['status' => 'Pendente'],
            'dados_novos' => ['status' => 'Concluído'],
        ]);

        $response = $this->actingAs($this->admin)->get(route('auditoria.show', $log->id));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auditoria/Show')
            ->has('log')
            ->where('log.id', $log->id)
            ->where('log.modulo', 'PEIs')
        );
    }

    public function test_edicao_ficha_aluno_captura_dados_anteriores_e_novos(): void
    {
        $this->actingAs($this->admin);

        $aluno = Aluno::create([
            'nome' => 'Matheus Henrique',
            'matricula' => '2026100999',
            'data_nascimento' => '2008-05-19',
            'is_active' => true,
        ]);

        $pei = Pei::create([
            'codigo' => 'PEI-2026-005',
            'aluno_id' => $aluno->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-28',
            'tempo_livre' => 'Leitura de ficção científica e C++',
            'pratica_esporte' => 'Caminhada',
            'caracteristicas_discente' => 'Metódico',
            'organizacao_e_prazos' => 'Cumpre prazos',
            'dificuldade_interacao_social' => 'Trabalho em duplas',
            'sugestoes_recursos_estrategias' => 'Checklists digitais',
            'is_active' => true,
        ]);

        // alteração do campo tempo_livre de c++ para python para validação do diff de auditoria
        $this->post(route('peis.update-ficha-aluno', $pei->id), [
            'tempo_livre' => 'Leitura de ficção científica e Python',
            'pratica_esporte' => 'Caminhada',
            'caracteristicas_discente' => 'Metódico',
            'organizacao_e_prazos' => 'Cumpre prazos',
            'dificuldade_interacao_social' => 'Trabalho em duplas',
            'sugestoes_recursos_estrategias' => 'Checklists digitais',
        ]);

        $log = AuditLog::where('modulo', 'PEIs')
            ->where('acao', 'Atualização')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('Leitura de ficção científica e C++', $log->dados_anteriores['tempo_livre']);
        $this->assertEquals('Leitura de ficção científica e Python', $log->dados_novos['tempo_livre']);
        $this->assertEquals('Caminhada', $log->dados_anteriores['pratica_esporte']);
        $this->assertEquals('Caminhada', $log->dados_novos['pratica_esporte']);
    }
}
