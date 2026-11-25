<?php

namespace App\Http\Controllers;

use App\Models\Disciplina;
use App\Models\Pei;
use App\Models\TurmaDisciplina;
use App\Services\AuditService;
use App\Services\PeiReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PeiReportController extends Controller
{
    public function __construct(private readonly PeiReportService $reportService)
    {
    }

    /**
     * gera e baixa o relatório do pei com as abas/seções selecionadas (pdf ou docx).
     */
    public function exportPei(Request $request, Pei $pei): Response
    {
        $validated = $request->validate([
            'formato' => ['required', 'string', 'in:pdf,docx'],
            'secoes' => ['nullable', 'array'],
            'secoes.*' => ['string', 'in:ficha_aluno,ficha_pei,nai'],
            'apoios' => ['nullable', 'array'],
            'apoios.*' => ['integer'],
            'disciplinas' => ['nullable', 'array'],
            'disciplinas.*' => ['integer'],
            'trimestres' => ['nullable', 'array'],
            'trimestres.*' => ['integer', 'in:1,2,3'],
        ]);

        $secoes = $validated['secoes'] ?? [];
        $apoios = $validated['apoios'] ?? [];
        $disciplinas = $validated['disciplinas'] ?? [];
        $trimestres = $validated['trimestres'] ?? [1, 2, 3];

        if (empty($secoes) && empty($apoios) && empty($disciplinas)) {
            abort(422, 'Selecione ao menos uma seção, equipe de apoio ou disciplina para gerar o relatório.');
        }

        $data = $this->reportService->buildReportData($pei, [
            'secoes' => $secoes,
            'apoios' => $apoios,
            'disciplinas' => $disciplinas,
            'trimestres' => $trimestres,
            'titulo_relatorio' => 'Relatório do Plano Educacional Individualizado (PEI)',
        ]);

        $formato = $validated['formato'];
        $alunoSlug = Str::slug($pei->aluno?->nome ?? 'aluno');
        $filename = "{$pei->codigo}-{$alunoSlug}.{$formato}";

        AuditService::log(
            'PEIs',
            'Exportação',
            "Gerou relatório completo do PEI {$pei->codigo} em formato " . strtoupper($formato),
            $pei,
            null,
            [
                'formato' => $formato,
                'secoes' => $secoes,
                'apoios' => $apoios,
                'disciplinas' => $disciplinas,
                'trimestres' => $trimestres,
            ]
        );

        return $this->downloadResponse($data, $formato, $filename);
    }

    /**
     * gera e baixa o relatório de uma disciplina específica do pei (pdf ou docx).
     */
    public function exportDisciplina(Request $request, Pei $pei, Disciplina $disciplina): Response
    {
        $validated = $request->validate([
            'formato' => ['required', 'string', 'in:pdf,docx'],
            'trimestre' => ['nullable', 'integer', 'in:1,2,3'],
        ]);

        // garante que a disciplina pertence à turma do pei ou possui registros vinculados
        $pertenceTurma = TurmaDisciplina::where('turma_id', $pei->turma_id)
            ->where('disciplina_id', $disciplina->id)
            ->exists();

        if (!$pertenceTurma) {
            abort(404, 'Disciplina não vinculada à turma deste PEI.');
        }

        $trimestre = isset($validated['trimestre']) ? (int) $validated['trimestre'] : null;
        $trimestres = $trimestre ? [$trimestre] : [1, 2, 3];

        $subtitulo = $trimestre
            ? "[{$disciplina->codigo}] {$disciplina->nome} — {$trimestre}º Trimestre"
            : "[{$disciplina->codigo}] {$disciplina->nome} — Todos os Trimestres";

        $data = $this->reportService->buildReportData($pei, [
            'secoes' => [],
            'apoios' => [],
            'disciplinas' => [$disciplina->id],
            'trimestres' => $trimestres,
            'titulo_relatorio' => 'Relatório de Acompanhamento de Disciplina — PEI',
            'subtitulo_relatorio' => $subtitulo,
        ]);

        $formato = $validated['formato'];
        $discSlug = Str::slug($disciplina->nome);
        $sufixoTri = $trimestre ? "-{$trimestre}T" : '';
        $filename = "{$pei->codigo}-{$discSlug}{$sufixoTri}.{$formato}";

        AuditService::log(
            'PEIs',
            'Exportação',
            "Gerou relatório da disciplina {$disciplina->nome}" . ($trimestre ? " ({$trimestre}º Trimestre)" : '') . " no PEI {$pei->codigo} em formato " . strtoupper($formato),
            $pei,
            null,
            [
                'formato' => $formato,
                'disciplina_id' => $disciplina->id,
                'trimestre' => $trimestre,
            ]
        );

        return $this->downloadResponse($data, $formato, $filename);
    }

    private function downloadResponse(array $data, string $formato, string $filename): Response
    {
        if ($formato === 'pdf') {
            $binary = $this->reportService->renderPdf($data);

            return response($binary, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $tempFile = $this->reportService->renderDocxToTempFile($data);

        return response()->download(
            $tempFile,
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]
        )->deleteFileAfterSend(true);
    }
}

