<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\AuditLog;
use App\Models\Disciplina;
use App\Models\Pei;
use App\Models\PeiApoio;
use App\Models\PeiDisciplina;
use App\Models\PeiIdentificacao;
use App\Models\PeiIntervencao;
use App\Models\PeiNaiAtividade;
use App\Models\Turma;
use App\Models\TurmaDisciplina;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class PeiReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminNai;
    protected User $professor;
    protected Aluno $aluno;
    protected Turma $turma;
    protected Disciplina $disciplinaMat;
    protected Disciplina $disciplinaBio;
    protected Disciplina $disciplinaFora;
    protected Pei $pei;
    protected PeiApoio $apoio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminNai = User::factory()->create([
            'name' => 'Gestora NAI',
            'cargo' => 'NAI',
            'is_admin' => true,
        ]);

        $this->professor = User::factory()->create([
            'name' => 'Prof. Carlos Alberto',
            'cargo' => 'Professor',
            'is_admin' => false,
        ]);

        $this->turma = Turma::create([
            'nome_curso' => 'Técnico em Informática 2026',
            'ano_letivo' => 2026,
            'is_active' => true,
        ]);

        $this->disciplinaMat = Disciplina::create([
            'codigo' => 'MAT-101',
            'nome' => 'Matemática Aplicada',
            'is_active' => true,
        ]);

        $this->disciplinaBio = Disciplina::create([
            'codigo' => 'BIO-102',
            'nome' => 'Biologia Celular',
            'is_active' => true,
        ]);

        $this->disciplinaFora = Disciplina::create([
            'codigo' => 'FIS-999',
            'nome' => 'Física Quântica',
            'is_active' => true,
        ]);

        $tdMat = TurmaDisciplina::create([
            'turma_id' => $this->turma->id,
            'disciplina_id' => $this->disciplinaMat->id,
        ]);
        $tdMat->professores()->attach($this->professor->id, ['is_substituto' => false]);

        TurmaDisciplina::create([
            'turma_id' => $this->turma->id,
            'disciplina_id' => $this->disciplinaBio->id,
        ]);

        $this->aluno = Aluno::create([
            'nome' => 'João Pedro Silva',
            'matricula' => '20260042',
            'data_nascimento' => '2009-03-10',
            'is_active' => true,
        ]);
        $this->aluno->turmas()->attach($this->turma->id, ['ano_letivo' => 2026, 'is_atual' => true]);

        $this->pei = Pei::create([
            'codigo' => 'PEI-2026-001',
            'aluno_id' => $this->aluno->id,
            'turma_id' => $this->turma->id,
            'ano_letivo' => 2026,
            'data_elaboracao' => '2026-02-20',
            'diagnostico' => 'TDAH e Dislexia',
            'tempo_livre' => 'Gosta de jogar xadrez e desenhar',
            'pratica_esporte' => 'Natação duas vezes por semana',
            'caracteristicas_discente' => 'Comunicativo e criativo',
            'organizacao_e_prazos' => 'Necessita lembretes visuais',
            'dificuldade_interacao_social' => 'Não apresenta dificuldades',
            'sugestoes_recursos_estrategias' => 'Mapas mentais e enunciados objetivos',
            'status' => 'Ativo',
            'is_active' => true,
        ]);

        PeiIdentificacao::create([
            'pei_id' => $this->pei->id,
            'tipo' => 'habilidade',
            'descricao' => 'Raciocínio lógico visual avançado',
            'ordem' => 0,
        ]);

        PeiIntervencao::create([
            'pei_id' => $this->pei->id,
            'demanda' => 'Tempo estendido em provas',
            'encaminhamento_estrategia' => 'Conceder 30 minutos adicionais',
            'responsavel' => 'Docentes e NAI',
            'ordem' => 0,
        ]);

        PeiNaiAtividade::create([
            'pei_id' => $this->pei->id,
            'trimestre' => 1,
            'objetivos' => 'Acompanhamento de rotina de estudos',
            'recursos_acoes' => 'Agenda semanal estruturada',
            'tempo' => '1º Trimestre',
            'avaliacao_resultados' => 'Boa adesão',
            'sugestoes_aproveitamento' => 'Manter revisão quinzenal',
            'ordem' => 0,
        ]);

        $this->apoio = PeiApoio::create([
            'pei_id' => $this->pei->id,
            'nome' => 'Apoio Psicopedagógico',
            'ordem' => 0,
        ]);

        $this->apoio->atividades()->create([
            'trimestre' => 1,
            'objetivos' => 'Treino de leitura ativa',
            'recursos_acoes' => 'Textos com fonte ampliada',
            'tempo' => 'Quinzenal',
            'ordem' => 0,
        ]);

        $peiMat = PeiDisciplina::create([
            'pei_id' => $this->pei->id,
            'disciplina_id' => $this->disciplinaMat->id,
            'trimestre' => 1,
            'grau_autonomia' => ['pouco_suporte', 'alta_compreensao'],
            'especifique_outros' => 'Portfólio digital',
        ]);

        $peiMat->planejamentos()->create([
            'objetivos' => 'Compreender funções de 1º grau',
            'conteudos' => 'Equações e gráficos lineares',
            'metodologias_recursos' => 'Uso de calculadora e gráficos coloridos',
            'estrategias_ensino' => ['Resolução de problemas', 'Orientações passo a passo'],
            'estrategias_avaliacao' => ['Tempo adicional para realização da avaliação'],
            'tempo' => 'Todo o trimestre',
            'tempo_opcao' => 'Todo o trimestre',
            'avaliacao_desempenho' => ['Atingiu o objetivo proposto com apoio/orientação.'],
            'sugestoes_aproveitamento' => 'Continuar uso de exemplos práticos',
            'ordem' => 0,
        ]);

        $peiMat->avaliacoes()->create([
            'tipo' => 'prova_escrita',
            'adotado' => true,
            'valor_1' => '10',
            'nota_1' => '8.5',
        ]);

        $peiMat->avaliacoes()->create([
            'tipo' => 'outros',
            'adotado' => true,
            'valor_1' => '5',
            'nota_1' => '4.5',
        ]);
    }

    private function extractDocxXml(string $binaryContent): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_docx_') . '.docx';
        file_put_contents($tmpFile, $binaryContent);

        $zip = new ZipArchive();
        $opened = $zip->open($tmpFile);
        $this->assertTrue($opened === true, 'O arquivo DOCX gerado não é um ZIP válido.');

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($tmpFile);

        $this->assertIsString($xml, 'word/document.xml não encontrado no DOCX.');

        return $xml;
    }

    public function test_can_export_full_pei_report_as_pdf(): void
    {
        $response = $this->actingAs($this->adminNai)->get(route('peis.relatorio', [
            'pei' => $this->pei->id,
            'formato' => 'pdf',
            'secoes' => ['ficha_aluno', 'ficha_pei', 'nai'],
            'apoios' => [$this->apoio->id],
            'disciplinas' => [$this->disciplinaMat->id, $this->disciplinaBio->id],
            'trimestres' => [1, 2, 3],
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());

        $this->assertDatabaseHas('audit_logs', [
            'modulo' => 'PEIs',
            'acao' => 'Exportação',
        ]);
    }

    public function test_can_export_selective_pei_report_as_docx(): void
    {
        $response = $this->actingAs($this->adminNai)->get(route('peis.relatorio', [
            'pei' => $this->pei->id,
            'formato' => 'docx',
            'secoes' => ['ficha_aluno'],
            'disciplinas' => [$this->disciplinaMat->id],
            'trimestres' => [1],
        ]));

        $response->assertOk();
        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );

        $xml = $this->extractDocxXml($response->streamedContent() ?: file_get_contents($response->getFile()->getPathname()));

        // Deve conter o que foi selecionado
        $this->assertStringContainsString('João Pedro Silva', $xml);
        $this->assertStringContainsString('Gosta de jogar xadrez e desenhar', $xml);
        $this->assertStringContainsString('MATEMÁTICA APLICADA', $xml);
        $this->assertStringContainsString('Compreender funções de 1º grau', $xml);
        $this->assertStringContainsString('Pouco suporte, Alta compreensão', $xml);
        $this->assertStringNotContainsString('pouco_suporte', $xml);
        $this->assertStringNotContainsString('alta_compreensao', $xml);
        $this->assertStringContainsString('Outros instrumentos (Portfólio digital)', $xml);

        // Não deve conter abas/disciplinas não selecionadas
        $this->assertStringNotContainsString('Raciocínio lógico visual avançado', $xml);
        $this->assertStringNotContainsString('Acompanhamento de rotina de estudos', $xml);
        $this->assertStringNotContainsString('APOIO PSICOPEDAGÓGICO', $xml);
        $this->assertStringNotContainsString('BIOLOGIA CELULAR', $xml);
    }

    public function test_pei_report_rejects_empty_selection(): void
    {
        $response = $this->actingAs($this->adminNai)->get(route('peis.relatorio', [
            'pei' => $this->pei->id,
            'formato' => 'pdf',
        ]));

        $response->assertStatus(422);
    }

    public function test_professor_can_export_individual_disciplina_as_pdf_and_docx(): void
    {
        $pdfResponse = $this->actingAs($this->professor)->get(route('peis.disciplina-relatorio', [
            'pei' => $this->pei->id,
            'disciplina' => $this->disciplinaMat->id,
            'formato' => 'pdf',
            'trimestre' => 1,
        ]));

        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        $docxResponse = $this->actingAs($this->professor)->get(route('peis.disciplina-relatorio', [
            'pei' => $this->pei->id,
            'disciplina' => $this->disciplinaMat->id,
            'formato' => 'docx',
            'trimestre' => 1,
        ]));

        $docxResponse->assertOk();
        $xml = $this->extractDocxXml(file_get_contents($docxResponse->getFile()->getPathname()));
        $this->assertStringContainsString('MATEMÁTICA APLICADA', $xml);
        $this->assertStringContainsString('Compreender funções de 1º grau', $xml);
        $this->assertStringNotContainsString('2º Trimestre', $xml);
    }

    public function test_disciplina_report_returns_404_when_disciplina_not_in_pei_turma(): void
    {
        $response = $this->actingAs($this->professor)->get(route('peis.disciplina-relatorio', [
            'pei' => $this->pei->id,
            'disciplina' => $this->disciplinaFora->id,
            'formato' => 'pdf',
        ]));

        $response->assertNotFound();
    }
}
