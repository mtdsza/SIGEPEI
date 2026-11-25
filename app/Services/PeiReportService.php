<?php

namespace App\Services;

use App\Models\Pei;
use App\Models\PeiApoio;
use App\Models\TurmaDisciplina;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class PeiReportService
{
    public const TIPOS_AVALIACAO_LABELS = [
        'prova_escrita' => 'Prova escrita',
        'trabalhos' => 'Trabalhos individuais / em grupo / seminários',
        'prova_pratica' => 'Prova prática de laboratório / campo',
        'outros' => 'Outros instrumentos',
        'prova_oral' => 'Prova oral',
    ];

    public const GRAU_AUTONOMIA_LABELS = [
        'muito_suporte' => 'Muito suporte',
        'pouco_suporte' => 'Pouco suporte',
        'alta_compreensao' => 'Alta compreensão',
        'pouca_compreensao' => 'Pouca compreensão',
    ];

    /**
     * monta todos os dados estruturados para geração de relatório (pdf ou docx).
     */
    public function buildReportData(Pei $pei, array $options = []): array
    {
        $pei->loadMissing([
            'aluno.deficiencias',
            'turma',
            'responsaveis.user',
            'identificacoes',
            'intervencoes',
            'naiAtividades',
            'apoios.responsaveis.user',
            'apoios.atividades',
            'disciplinasTrimestres.planejamentos',
            'disciplinasTrimestres.avaliacoes',
        ]);

        $secoes = $options['secoes'] ?? ['ficha_aluno', 'ficha_pei', 'nai'];
        $apoiosIds = array_map('intval', $options['apoios'] ?? []);
        $disciplinasIds = array_map('intval', $options['disciplinas'] ?? []);
        $trimestres = array_values(array_unique(array_filter(
            array_map('intval', $options['trimestres'] ?? [1, 2, 3]),
            fn (int $t) => in_array($t, [1, 2, 3], true)
        )));
        sort($trimestres);
        if (empty($trimestres)) {
            $trimestres = [1, 2, 3];
        }

        $idadeNaElaboracao = '-';
        if ($pei->aluno?->data_nascimento && $pei->data_elaboracao) {
            $nasc = Carbon::parse($pei->aluno->data_nascimento);
            $elab = Carbon::parse($pei->data_elaboracao);
            $idadeNaElaboracao = ((int) $nasc->diffInYears($elab)) . ' anos';
        }

        $responsaveisFichaInicial = $pei->responsaveis
            ->where('secao', 'ficha_inicial')
            ->map(fn ($r) => $r->nome_exibicao)
            ->filter()
            ->values()
            ->toArray();

        $incluirFichaAluno = in_array('ficha_aluno', $secoes, true);
        $incluirFichaPei = in_array('ficha_pei', $secoes, true);
        $incluirNai = in_array('nai', $secoes, true);

        $identificacoes = [
            'habilidades' => $pei->identificacoes->where('tipo', 'habilidade')->pluck('descricao')->values()->toArray(),
            'dificuldades' => $pei->identificacoes->where('tipo', 'dificuldade')->pluck('descricao')->values()->toArray(),
            'dificultadores_internos' => $pei->identificacoes->where('tipo', 'dificultador_interno')->pluck('descricao')->values()->toArray(),
            'facilitadores_internos' => $pei->identificacoes->where('tipo', 'facilitador_interno')->pluck('descricao')->values()->toArray(),
            'dificultadores_externos' => $pei->identificacoes->where('tipo', 'dificultador_externo')->pluck('descricao')->values()->toArray(),
            'facilitadores_externos' => $pei->identificacoes->where('tipo', 'facilitador_externo')->pluck('descricao')->values()->toArray(),
        ];

        $intervencoes = $pei->intervencoes->map(fn ($i) => [
            'demanda' => $i->demanda,
            'encaminhamento_estrategia' => $i->encaminhamento_estrategia,
            'responsavel' => $i->responsavel,
        ])->values()->toArray();

        $naiTrimestres = [];
        if ($incluirNai) {
            foreach ($trimestres as $t) {
                $naiTrimestres[$t] = [
                    'trimestre' => $t,
                    'responsaveis' => $pei->responsaveis
                        ->where('secao', "nai_{$t}")
                        ->map(fn ($r) => $r->nome_exibicao)
                        ->filter()
                        ->values()
                        ->toArray(),
                    'atividades' => $pei->naiAtividades
                        ->where('trimestre', $t)
                        ->map(fn ($atv) => [
                            'objetivos' => $atv->objetivos,
                            'recursos_acoes' => $atv->recursos_acoes,
                            'tempo' => $atv->tempo,
                            'avaliacao_resultados' => $atv->avaliacao_resultados,
                            'sugestoes_aproveitamento' => $atv->sugestoes_aproveitamento,
                        ])
                        ->values()
                        ->toArray(),
                ];
            }
        }

        $equipesApoio = [];
        if (!empty($apoiosIds)) {
            $equipesApoio = $pei->apoios
                ->filter(fn (PeiApoio $a) => in_array((int) $a->id, $apoiosIds, true))
                ->map(function (PeiApoio $apoio) use ($trimestres) {
                    $tris = [];
                    foreach ($trimestres as $t) {
                        $tris[$t] = [
                            'trimestre' => $t,
                            'responsaveis' => $apoio->responsaveis
                                ->where('trimestre', $t)
                                ->map(fn ($r) => $r->nome_exibicao)
                                ->filter()
                                ->values()
                                ->toArray(),
                            'atividades' => $apoio->atividades
                                ->where('trimestre', $t)
                                ->map(fn ($atv) => [
                                    'objetivos' => $atv->objetivos,
                                    'recursos_acoes' => $atv->recursos_acoes,
                                    'tempo' => $atv->tempo,
                                    'avaliacao_resultados' => $atv->avaliacao_resultados,
                                    'sugestoes_aproveitamento' => $atv->sugestoes_aproveitamento,
                                ])
                                ->values()
                                ->toArray(),
                        ];
                    }

                    return [
                        'id' => $apoio->id,
                        'nome' => $apoio->nome,
                        'trimestres' => $tris,
                    ];
                })
                ->values()
                ->toArray();
        }

        $disciplinasData = [];
        if (!empty($disciplinasIds)) {
            $turmaDisciplinas = TurmaDisciplina::with(['disciplina', 'professores'])
                ->where('turma_id', $pei->turma_id)
                ->whereIn('disciplina_id', $disciplinasIds)
                ->get();

            foreach ($turmaDisciplinas as $td) {
                $disciplina = $td->disciplina;
                if (!$disciplina) {
                    continue;
                }

                $professores = $td->professores->map(function ($prof) {
                    $suffix = $prof->pivot->is_substituto ? ' (Substituto)' : '';

                    return $prof->name . $suffix;
                })->values()->toArray();

                $tris = [];
                foreach ($trimestres as $t) {
                    $peiDisc = $pei->disciplinasTrimestres
                        ->where('disciplina_id', $disciplina->id)
                        ->where('trimestre', $t)
                        ->first();

                    $planejamentos = $peiDisc ? $peiDisc->planejamentos->map(function ($p) {
                        $tempo = ($p->tempo_opcao === 'Outro' ? $p->tempo_outro : $p->tempo_opcao) ?: $p->tempo;

                        return [
                            'objetivos' => $p->objetivos ?? '',
                            'conteudos' => $p->conteudos ?? '',
                            'metodologias_recursos' => $p->metodologias_recursos ?? '',
                            'estrategias_ensino' => is_array($p->estrategias_ensino) ? $p->estrategias_ensino : [],
                            'estrategias_ensino_outros' => $p->estrategias_ensino_outros ?? '',
                            'estrategias_avaliacao' => is_array($p->estrategias_avaliacao) ? $p->estrategias_avaliacao : [],
                            'estrategias_avaliacao_outros' => $p->estrategias_avaliacao_outros ?? '',
                            'tempo' => $tempo ?? '',
                            'avaliacao_resultados' => $p->avaliacao_resultados ?? '',
                            'avaliacao_desempenho' => is_array($p->avaliacao_desempenho) ? $p->avaliacao_desempenho : [],
                            'avaliacao_desempenho_outros' => $p->avaliacao_desempenho_outros ?? '',
                            'sugestoes_aproveitamento' => $p->sugestoes_aproveitamento ?? '',
                        ];
                    })->values()->toArray() : [];

                    $especifiqueOutros = trim((string) ($peiDisc?->especifique_outros ?? ''));
                    $avaliacoes = [];
                    foreach (self::TIPOS_AVALIACAO_LABELS as $tipoKey => $label) {
                        $av = $peiDisc?->avaliacoes->where('tipo', $tipoKey)->first();
                        $labelFinal = ($tipoKey === 'outros' && $especifiqueOutros !== '')
                            ? "{$label} ({$especifiqueOutros})"
                            : $label;

                        $avaliacoes[] = [
                            'tipo' => $tipoKey,
                            'label' => $labelFinal,
                            'adotado' => (bool) ($av?->adotado ?? false),
                            'valor_1' => $av?->valor_1 ?? '',
                            'nota_1' => $av?->nota_1 ?? '',
                            'valor_2' => $av?->valor_2 ?? '',
                            'nota_2' => $av?->nota_2 ?? '',
                            'valor_3' => $av?->valor_3 ?? '',
                            'nota_3' => $av?->nota_3 ?? '',
                        ];
                    }

                    $grauAutonomiaFormatado = $this->formatGrauAutonomia(
                        is_array($peiDisc?->grau_autonomia) ? $peiDisc->grau_autonomia : []
                    );

                    $tris[$t] = [
                        'trimestre' => $t,
                        'grau_autonomia' => $grauAutonomiaFormatado,
                        'especifique_outros' => $especifiqueOutros,
                        'planejamentos' => $planejamentos,
                        'avaliacoes' => $avaliacoes,
                        'tem_registros' => !empty($planejamentos)
                            || !empty($grauAutonomiaFormatado)
                            || collect($avaliacoes)->contains('adotado', true),
                    ];
                }

                $disciplinasData[] = [
                    'id' => $disciplina->id,
                    'codigo' => $disciplina->codigo,
                    'nome' => $disciplina->nome,
                    'professores' => $professores,
                    'trimestres' => $tris,
                ];
            }
        }

        return [
            'titulo_relatorio' => $options['titulo_relatorio'] ?? 'Relatório do Plano Educacional Individualizado (PEI)',
            'subtitulo_relatorio' => $options['subtitulo_relatorio'] ?? null,
            'gerado_em' => Carbon::now()->format('d/m/Y \à\s H:i'),
            'trimestres_selecionados' => $trimestres,
            'pei' => [
                'id' => $pei->id,
                'codigo' => $pei->codigo,
                'ano_letivo' => $pei->ano_letivo,
                'data_elaboracao' => $pei->data_elaboracao ? Carbon::parse($pei->data_elaboracao)->format('d/m/Y') : '-',
                'idade_na_elaboracao' => $idadeNaElaboracao,
                'diagnostico' => $pei->diagnostico ?: 'Nenhum diagnóstico informado no PEI.',
                'status' => $pei->status,
                'aluno' => [
                    'nome' => $pei->aluno?->nome ?? 'Não informado',
                    'matricula' => $pei->aluno?->matricula ?? '-',
                    'data_nascimento' => $pei->aluno?->data_nascimento ? Carbon::parse($pei->aluno->data_nascimento)->format('d/m/Y') : '-',
                    'deficiencias' => $pei->aluno?->deficiencias->map(fn ($d) => "[{$d->codigo}] {$d->nome}")->values()->toArray() ?? [],
                ],
                'turma' => $pei->turma?->nome_curso ?? 'Turma não informada',
                'responsaveis_ficha_inicial' => $responsaveisFichaInicial,
                'ficha_aluno' => [
                    'tempo_livre' => $pei->tempo_livre ?: 'Não informado neste PEI.',
                    'pratica_esporte' => $pei->pratica_esporte ?: 'Não informado neste PEI.',
                    'caracteristicas_discente' => $pei->caracteristicas_discente ?: 'Não informado neste PEI.',
                    'organizacao_e_prazos' => $pei->organizacao_e_prazos ?: 'Não informado neste PEI.',
                    'dificuldade_interacao_social' => $pei->dificuldade_interacao_social ?: 'Não informado neste PEI.',
                    'sugestoes_recursos_estrategias' => $pei->sugestoes_recursos_estrategias ?: 'Não informado neste PEI.',
                ],
            ],
            'incluir' => [
                'ficha_aluno' => $incluirFichaAluno,
                'ficha_pei' => $incluirFichaPei,
                'nai' => $incluirNai,
            ],
            'identificacoes' => $identificacoes,
            'intervencoes' => $intervencoes,
            'nai_trimestres' => $naiTrimestres,
            'equipes_apoio' => $equipesApoio,
            'disciplinas' => $disciplinasData,
        ];
    }

    /**
     * gera o conteúdo binário do relatório em pdf via dompdf.
     */
    public function renderPdf(array $data): string
    {
        $pdf = Pdf::loadView('reports.pei', ['data' => $data])
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        return $pdf->output();
    }

    /**
     * gera o arquivo docx em caminho temporário e retorna o path absoluto.
     */
    public function renderDocxToTempFile(array $data): string
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'pageSizeW' => Converter::cmToTwip(21.0),
            'pageSizeH' => Converter::cmToTwip(29.7),
            'marginTop' => Converter::cmToTwip(1.8),
            'marginBottom' => Converter::cmToTwip(1.8),
            'marginLeft' => Converter::cmToTwip(1.8),
            'marginRight' => Converter::cmToTwip(1.8),
        ]);

        // cabeçalho e rodapé
        $header = $section->addHeader();
        $header->addText(
            $this->clean("SIGEPEI — {$data['pei']['codigo']} | {$data['pei']['aluno']['nome']}"),
            ['size' => 8, 'color' => '64748B'],
            ['alignment' => Jc::END]
        );

        $footer = $section->addFooter();
        $footer->addPreserveText(
            $this->clean("Gerado em {$data['gerado_em']} — Página {PAGE} de {NUMPAGES}"),
            ['size' => 8, 'color' => '64748B'],
            ['alignment' => Jc::CENTER]
        );

        // estilos comuns
        $verde = '006633';
        $cinzaEscuro = '1E293B';
        $cinzaMedio = '475569';
        $fundoCabecalhoTabela = '006633';
        $fundoSubCabecalho = 'E2E8F0';
        $fundoCinzaClaro = 'F8FAFC';

        $tableStyle = [
            'borderSize' => 4,
            'borderColor' => 'CBD5E1',
            'cellMargin' => 80,
            'width' => 5000,
            'unit' => TblWidth::PERCENT,
        ];

        $headerCellStyle = ['bgColor' => $fundoCabecalhoTabela, 'valign' => 'center'];
        $headerTextStyle = ['bold' => true, 'size' => 9, 'color' => 'FFFFFF'];
        $subHeaderCellStyle = ['bgColor' => $fundoSubCabecalho, 'valign' => 'center'];
        $labelStyle = ['bold' => true, 'size' => 9, 'color' => $cinzaEscuro];
        $valueStyle = ['size' => 9.5, 'color' => $cinzaEscuro];
        $mutedStyle = ['italic' => true, 'size' => 9, 'color' => $cinzaMedio];

        // título principal
        $section->addText(
            $this->clean($data['titulo_relatorio']),
            ['bold' => true, 'size' => 15, 'color' => $verde],
            ['spaceAfter' => 60]
        );

        if (!empty($data['subtitulo_relatorio'])) {
            $section->addText(
                $this->clean($data['subtitulo_relatorio']),
                ['bold' => true, 'size' => 11, 'color' => $cinzaMedio],
                ['spaceAfter' => 120]
            );
        }

        // bloco de identificação geral do pei
        $this->addDocxSectionTitle($section, 'DADOS GERAIS DO PEI', $verde);
        $infoTable = $section->addTable($tableStyle);

        $this->addDocxKeyValueRow($infoTable, 'Código do PEI', $data['pei']['codigo'], 'Status / Ano Letivo', ($data['pei']['status'] ?: 'Ativo') . ' — ' . $data['pei']['ano_letivo'], $fundoCinzaClaro, $labelStyle, $valueStyle);
        $this->addDocxKeyValueRow($infoTable, 'Discente', $data['pei']['aluno']['nome'], 'Matrícula', $data['pei']['aluno']['matricula'], $fundoCinzaClaro, $labelStyle, $valueStyle);
        $this->addDocxKeyValueRow($infoTable, 'Curso / Turma', $data['pei']['turma'], 'Data de Nascimento', $data['pei']['aluno']['data_nascimento'], $fundoCinzaClaro, $labelStyle, $valueStyle);
        $this->addDocxKeyValueRow($infoTable, 'Data de Elaboração', $data['pei']['data_elaboracao'], 'Idade na Elaboração', $data['pei']['idade_na_elaboracao'], $fundoCinzaClaro, $labelStyle, $valueStyle);

        $rowDiag = $infoTable->addRow();
        $rowDiag->addCell(2200, ['bgColor' => $fundoCinzaClaro])->addText($this->clean('Diagnóstico / Deficiências'), $labelStyle);
        $diagText = $data['pei']['diagnostico'];
        if (!empty($data['pei']['aluno']['deficiencias'])) {
            $diagText .= "\nDeficiências vinculadas: " . implode(', ', $data['pei']['aluno']['deficiencias']);
        }
        $this->addMultilineText($rowDiag->addCell(7800, ['gridSpan' => 3]), $diagText, $valueStyle);

        $rowResp = $infoTable->addRow();
        $rowResp->addCell(2200, ['bgColor' => $fundoCinzaClaro])->addText($this->clean('Responsáveis pela Elaboração'), $labelStyle);
        $respTexto = !empty($data['pei']['responsaveis_ficha_inicial'])
            ? implode(', ', $data['pei']['responsaveis_ficha_inicial'])
            : 'Nenhum responsável registrado.';
        $rowResp->addCell(7800, ['gridSpan' => 3])->addText($this->clean($respTexto), $valueStyle);

        $section->addTextBreak(1);

        // 1. Ficha do Aluno
        if ($data['incluir']['ficha_aluno']) {
            $this->addDocxSectionTitle($section, 'FICHA DO ALUNO', $verde);
            $fichaTable = $section->addTable($tableStyle);

            $perguntasFicha = [
                'O que gosta de fazer no tempo livre?' => $data['pei']['ficha_aluno']['tempo_livre'],
                'Gosta de praticar esporte?' => $data['pei']['ficha_aluno']['pratica_esporte'],
                'Características do discente' => $data['pei']['ficha_aluno']['caracteristicas_discente'],
                'O aluno é organizado? Realiza a entrega de atividades avaliativas dentro do prazo estipulado pelo professor?' => $data['pei']['ficha_aluno']['organizacao_e_prazos'],
                'Apresenta dificuldade na interação social?' => $data['pei']['ficha_aluno']['dificuldade_interacao_social'],
                'Sugestões de recursos ou estratégias para auxiliar na aprendizagem' => $data['pei']['ficha_aluno']['sugestoes_recursos_estrategias'],
            ];

            foreach ($perguntasFicha as $pergunta => $resposta) {
                $r = $fichaTable->addRow();
                $r->addCell(3200, ['bgColor' => $fundoCinzaClaro])->addText($this->clean($pergunta), $labelStyle);
                $this->addMultilineText($r->addCell(6800), $resposta, $valueStyle);
            }

            $section->addTextBreak(1);
        }

        // 2. Ficha do PEI
        if ($data['incluir']['ficha_pei']) {
            $this->addDocxSectionTitle($section, 'FICHA DO PEI — IDENTIFICAÇÕES E INTERVENÇÕES', $verde);

            $identTable = $section->addTable($tableStyle);
            $blocosIdent = [
                'Suas Habilidades' => $data['identificacoes']['habilidades'],
                'Suas Dificuldades' => $data['identificacoes']['dificuldades'],
                'Dificultadores internos do campus' => $data['identificacoes']['dificultadores_internos'],
                'Facilitadores internos do campus' => $data['identificacoes']['facilitadores_internos'],
                'Dificultadores externos ao campus' => $data['identificacoes']['dificultadores_externos'],
                'Facilitadores externos ao campus' => $data['identificacoes']['facilitadores_externos'],
            ];

            foreach ($blocosIdent as $titulo => $itens) {
                $r = $identTable->addRow();
                $r->addCell(3200, ['bgColor' => $fundoCinzaClaro])->addText($this->clean($titulo), $labelStyle);
                $cell = $r->addCell(6800);
                if (!empty($itens)) {
                    foreach ($itens as $item) {
                        $cell->addText($this->clean("• {$item}"), $valueStyle, ['spaceAfter' => 30]);
                    }
                } else {
                    $cell->addText('Nenhum item registrado.', $mutedStyle);
                }
            }

            $section->addTextBreak(1);
            $section->addText($this->clean('Possibilidades de Intervenção'), ['bold' => true, 'size' => 10.5, 'color' => $cinzaEscuro], ['spaceAfter' => 60]);

            $intTable = $section->addTable($tableStyle);
            $hRow = $intTable->addRow(null, ['tblHeader' => true]);
            $hRow->addCell(3300, $headerCellStyle)->addText('Demandas', $headerTextStyle);
            $hRow->addCell(4000, $headerCellStyle)->addText('Encaminhamento(s) / Estratégia(s)', $headerTextStyle);
            $hRow->addCell(2700, $headerCellStyle)->addText('Responsável(is)', $headerTextStyle);

            if (!empty($data['intervencoes'])) {
                foreach ($data['intervencoes'] as $interv) {
                    $r = $intTable->addRow();
                    $this->addMultilineText($r->addCell(3300), $interv['demanda'] ?: '-', $valueStyle);
                    $this->addMultilineText($r->addCell(4000), $interv['encaminhamento_estrategia'] ?: '-', $valueStyle);
                    $this->addMultilineText($r->addCell(2700), $interv['responsavel'] ?: '-', $valueStyle);
                }
            } else {
                $r = $intTable->addRow();
                $r->addCell(10000, ['gridSpan' => 3])->addText('Nenhuma intervenção registrada.', $mutedStyle);
            }

            $section->addTextBreak(1);
        }

        // 3. NAI (Trimestres)
        if ($data['incluir']['nai']) {
            $this->addDocxSectionTitle($section, 'ACOMPANHAMENTO DO NAI (TRIMESTRES)', $verde);
            $this->addDocxAcompanhamentoTrimestres($section, $data['nai_trimestres'], $tableStyle, $headerCellStyle, $headerTextStyle, $fundoCinzaClaro, $labelStyle, $valueStyle, $mutedStyle);
            $section->addTextBreak(1);
        }

        // 4. Equipes de Apoio
        foreach ($data['equipes_apoio'] as $apoio) {
            $this->addDocxSectionTitle($section, 'EQUIPE DE APOIO: ' . mb_strtoupper($apoio['nome']), $verde);
            $this->addDocxAcompanhamentoTrimestres($section, $apoio['trimestres'], $tableStyle, $headerCellStyle, $headerTextStyle, $fundoCinzaClaro, $labelStyle, $valueStyle, $mutedStyle);
            $section->addTextBreak(1);
        }

        // 5. Disciplinas
        foreach ($data['disciplinas'] as $disc) {
            $tituloDisc = "[{$disc['codigo']}] {$disc['nome']}";
            $this->addDocxSectionTitle($section, 'DISCIPLINA: ' . mb_strtoupper($tituloDisc), $verde);

            $profTexto = !empty($disc['professores']) ? implode(', ', $disc['professores']) : 'Não informado';
            $section->addText($this->clean("Professor(es): {$profTexto}"), ['bold' => true, 'size' => 9.5, 'color' => $cinzaMedio], ['spaceAfter' => 100]);

            foreach ($disc['trimestres'] as $triNum => $tri) {
                $section->addText(
                    $this->clean("{$triNum}º Trimestre"),
                    ['bold' => true, 'size' => 11, 'color' => $verde],
                    ['spaceBefore' => 80, 'spaceAfter' => 60]
                );

                if (!$tri['tem_registros']) {
                    $section->addText('Nenhum planejamento ou avaliação registrado para este trimestre.', $mutedStyle, ['spaceAfter' => 100]);
                    continue;
                }

                if (!empty($tri['grau_autonomia'])) {
                    $autTexto = implode(', ', $tri['grau_autonomia']);
                    $section->addText($this->clean("Grau de autonomia para realizar as atividades: {$autTexto}"), $valueStyle, ['spaceAfter' => 80]);
                }

                // planejamentos pedagógicos
                if (!empty($tri['planejamentos'])) {
                    foreach ($tri['planejamentos'] as $pIdx => $plan) {
                        $planTable = $section->addTable($tableStyle);

                        $hRow = $planTable->addRow(null, ['tblHeader' => true]);
                        $hRow->addCell(10000, ['gridSpan' => 2, 'bgColor' => $fundoSubCabecalho])
                            ->addText($this->clean('Planejamento Pedagógico #' . ($pIdx + 1)), ['bold' => true, 'size' => 9.5, 'color' => $cinzaEscuro]);

                        $this->addDocxSimpleRow($planTable, 'Objetivos Acadêmicos', $plan['objetivos'] ?: '-', $fundoCinzaClaro, $labelStyle, $valueStyle);
                        $this->addDocxSimpleRow($planTable, 'Conteúdos Curriculares Priorizados', $plan['conteudos'] ?: '-', $fundoCinzaClaro, $labelStyle, $valueStyle);

                        // estratégias de ensino e avaliação
                        $estEnsino = $this->formatChecklistText($plan['estrategias_ensino'], $plan['estrategias_ensino_outros']);
                        $estAval = $this->formatChecklistText($plan['estrategias_avaliacao'], $plan['estrategias_avaliacao_outros']);

                        if ($estEnsino !== '' || $estAval !== '') {
                            if ($estEnsino !== '') {
                                $this->addDocxSimpleRow($planTable, 'Estratégias de Ensino', $estEnsino, $fundoCinzaClaro, $labelStyle, $valueStyle);
                            }
                            if ($estAval !== '') {
                                $this->addDocxSimpleRow($planTable, 'Estratégias e Condições de Avaliação', $estAval, $fundoCinzaClaro, $labelStyle, $valueStyle);
                            }
                        } elseif (!empty($plan['metodologias_recursos'])) {
                            $this->addDocxSimpleRow($planTable, 'Metodologias / Estratégias / Recursos', $plan['metodologias_recursos'], $fundoCinzaClaro, $labelStyle, $valueStyle);
                        }

                        $this->addDocxSimpleRow($planTable, 'Tempo / Prazo', $plan['tempo'] ?: '-', $fundoCinzaClaro, $labelStyle, $valueStyle);

                        $desempenho = $this->formatChecklistText($plan['avaliacao_desempenho'], $plan['avaliacao_desempenho_outros']);
                        if ($desempenho === '' && !empty($plan['avaliacao_resultados'])) {
                            $desempenho = $plan['avaliacao_resultados'];
                        }
                        $this->addDocxSimpleRow($planTable, 'Avaliação de Desempenho / Resultados', $desempenho ?: '-', $fundoCinzaClaro, $labelStyle, $valueStyle);
                        $this->addDocxSimpleRow($planTable, 'Sugestões para Melhor Aproveitamento', $plan['sugestoes_aproveitamento'] ?: '-', $fundoCinzaClaro, $labelStyle, $valueStyle);

                        $section->addTextBreak(1);
                    }
                }

                // instrumentos de avaliação
                $adotados = array_values(array_filter($tri['avaliacoes'], fn ($a) => $a['adotado']));
                $section->addText('Instrumentos de Avaliação Adotados', ['bold' => true, 'size' => 9.5, 'color' => $cinzaEscuro], ['spaceAfter' => 50]);

                if (!empty($adotados)) {
                    $avTable = $section->addTable($tableStyle);
                    $avHeader = $avTable->addRow(null, ['tblHeader' => true]);
                    $avHeader->addCell(4000, $headerCellStyle)->addText('Instrumento', $headerTextStyle);
                    $avHeader->addCell(2000, $headerCellStyle)->addText('1º Valor / Nota', $headerTextStyle);
                    $avHeader->addCell(2000, $headerCellStyle)->addText('2º Valor / Nota', $headerTextStyle);
                    $avHeader->addCell(2000, $headerCellStyle)->addText('3º Valor / Nota', $headerTextStyle);

                    foreach ($adotados as $av) {
                        $r = $avTable->addRow();
                        $r->addCell(4000)->addText($this->clean($av['label']), $valueStyle);
                        $r->addCell(2000)->addText($this->clean($this->formatNotaPar($av['valor_1'], $av['nota_1'])), $valueStyle);
                        $r->addCell(2000)->addText($this->clean($this->formatNotaPar($av['valor_2'], $av['nota_2'])), $valueStyle);
                        $r->addCell(2000)->addText($this->clean($this->formatNotaPar($av['valor_3'], $av['nota_3'])), $valueStyle);
                    }
                } else {
                    $section->addText('Nenhum instrumento de avaliação marcado como adotado neste trimestre.', $mutedStyle);
                }

                $section->addTextBreak(1);
            }
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'pei_report_') . '.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return $tempPath;
    }

    private function addDocxSectionTitle($section, string $title, string $color): void
    {
        $section->addText(
            $this->clean($title),
            ['bold' => true, 'size' => 11.5, 'color' => $color],
            [
                'spaceBefore' => 120,
                'spaceAfter' => 80,
                'borderBottomSize' => 10,
                'borderBottomColor' => $color,
            ]
        );
    }

    private function addDocxKeyValueRow($table, string $k1, string $v1, string $k2, string $v2, string $bg, array $labelStyle, array $valueStyle): void
    {
        $row = $table->addRow();
        $row->addCell(2200, ['bgColor' => $bg])->addText($this->clean($k1), $labelStyle);
        $row->addCell(3300)->addText($this->clean($v1), $valueStyle);
        $row->addCell(2000, ['bgColor' => $bg])->addText($this->clean($k2), $labelStyle);
        $row->addCell(2500)->addText($this->clean($v2), $valueStyle);
    }

    private function addDocxSimpleRow($table, string $label, string $value, string $bg, array $labelStyle, array $valueStyle): void
    {
        $row = $table->addRow();
        $row->addCell(3000, ['bgColor' => $bg])->addText($this->clean($label), $labelStyle);
        $this->addMultilineText($row->addCell(7000), $value, $valueStyle);
    }

    private function addDocxAcompanhamentoTrimestres($section, array $trimestresData, array $tableStyle, array $headerCellStyle, array $headerTextStyle, string $fundoCinzaClaro, array $labelStyle, array $valueStyle, array $mutedStyle): void
    {
        foreach ($trimestresData as $triNum => $tri) {
            $section->addText(
                $this->clean("{$triNum}º Trimestre"),
                ['bold' => true, 'size' => 10.5, 'color' => '006633'],
                ['spaceBefore' => 80, 'spaceAfter' => 50]
            );

            $respText = !empty($tri['responsaveis']) ? implode(', ', $tri['responsaveis']) : 'Nenhum responsável registrado.';
            $section->addText($this->clean("Responsáveis: {$respText}"), ['size' => 9, 'color' => '334155'], ['spaceAfter' => 60]);

            $table = $section->addTable($tableStyle);
            $hRow = $table->addRow(null, ['tblHeader' => true]);
            $hRow->addCell(2100, $headerCellStyle)->addText('Objetivos', $headerTextStyle);
            $hRow->addCell(2400, $headerCellStyle)->addText('Recursos / Ações', $headerTextStyle);
            $hRow->addCell(1300, $headerCellStyle)->addText('Tempo', $headerTextStyle);
            $hRow->addCell(2100, $headerCellStyle)->addText('Avaliação / Resultados', $headerTextStyle);
            $hRow->addCell(2100, $headerCellStyle)->addText('Sugestões', $headerTextStyle);

            if (!empty($tri['atividades'])) {
                foreach ($tri['atividades'] as $atv) {
                    $r = $table->addRow();
                    $this->addMultilineText($r->addCell(2100), $atv['objetivos'] ?: '-', $valueStyle);
                    $this->addMultilineText($r->addCell(2400), $atv['recursos_acoes'] ?: '-', $valueStyle);
                    $this->addMultilineText($r->addCell(1300), $atv['tempo'] ?: '-', $valueStyle);
                    $this->addMultilineText($r->addCell(2100), $atv['avaliacao_resultados'] ?: '-', $valueStyle);
                    $this->addMultilineText($r->addCell(2100), $atv['sugestoes_aproveitamento'] ?: '-', $valueStyle);
                }
            } else {
                $r = $table->addRow();
                $r->addCell(10000, ['gridSpan' => 5])->addText("Nenhuma atividade registrada para o {$triNum}º Trimestre.", $mutedStyle);
            }

            $section->addTextBreak(1);
        }
    }

    private function addMultilineText($cell, string $text, array $style): void
    {
        $lines = preg_split("/\r\n|\r|\n/", $text) ?: [''];
        foreach ($lines as $idx => $line) {
            $cell->addText($this->clean($line), $style, ['spaceAfter' => $idx < count($lines) - 1 ? 20 : 0]);
        }
    }

    private function formatGrauAutonomia(array $values): array
    {
        $result = [];
        foreach ($values as $value) {
            $raw = trim((string) $value);
            if ($raw === '') {
                continue;
            }
            if (isset(self::GRAU_AUTONOMIA_LABELS[$raw])) {
                $result[] = self::GRAU_AUTONOMIA_LABELS[$raw];
            } else {
                $normalized = str_replace(['_', '-'], ' ', $raw);
                $result[] = mb_strtoupper(mb_substr($normalized, 0, 1)) . mb_substr($normalized, 1);
            }
        }

        return array_values(array_unique($result));
    }

    private function formatChecklistText(array $items, string $outros): string
    {
        $parts = [];
        foreach ($items as $item) {
            if (trim((string) $item) !== '') {
                $parts[] = '• ' . trim((string) $item);
            }
        }
        if (trim($outros) !== '') {
            $parts[] = 'Outros: ' . trim($outros);
        }

        return implode("\n", $parts);
    }

    private function formatNotaPar(string $valor, string $nota): string
    {
        if ($valor === '' && $nota === '') {
            return '-';
        }
        $v = $valor !== '' ? $valor : '-';
        $n = $nota !== '' ? $nota : '-';

        return "Valor: {$v} | Nota: {$n}";
    }

    private function clean(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        // remove caracteres de controle inválidos em xml 1.0
        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? $text;

        return htmlspecialchars($sanitized, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}

