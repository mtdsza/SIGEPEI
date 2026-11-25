<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $data['titulo_relatorio'] }} - {{ $data['pei']['codigo'] }}</title>
    <style>
        @page {
            margin: 18mm 15mm 18mm 15mm;
        }
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #006633;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header-top {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #475569;
            margin-bottom: 2px;
        }
        .header h1 {
            margin: 0;
            font-size: 14px;
            color: #006633;
            text-transform: uppercase;
        }
        .header .subtitle {
            margin-top: 3px;
            font-size: 10px;
            color: #334155;
            font-weight: bold;
        }
        .header .meta {
            margin-top: 4px;
            font-size: 8px;
            color: #64748b;
        }
        .section-title {
            background-color: #006633;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 8px;
            margin-top: 14px;
            margin-bottom: 8px;
            letter-spacing: 0.4px;
        }
        .subsection-title {
            font-size: 10px;
            font-weight: bold;
            color: #006633;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin-top: 10px;
            margin-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            vertical-align: top;
            text-align: left;
            word-wrap: break-word;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
            font-size: 8.5px;
            text-transform: uppercase;
        }
        .table-header-green th {
            background-color: #006633;
            color: #ffffff;
        }
        .label-cell {
            background-color: #f8fafc;
            font-weight: bold;
            color: #334155;
            width: 24%;
        }
        .muted {
            color: #64748b;
            font-style: italic;
        }
        .box {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 6px 8px;
            margin-bottom: 8px;
        }
        ul.clean-list {
            margin: 2px 0 2px 14px;
            padding: 0;
        }
        ul.clean-list li {
            margin-bottom: 2px;
        }
        .badge {
            display: inline-block;
            background-color: #ecfdf5;
            color: #006633;
            border: 1px solid #a7f3d0;
            padding: 1px 5px;
            font-size: 8px;
            font-weight: bold;
            margin-right: 4px;
            margin-bottom: 2px;
        }
        .avoid-break {
            page-break-inside: avoid;
        }
        .footer {
            position: fixed;
            bottom: -12mm;
            left: 0;
            right: 0;
            height: 10mm;
            font-size: 8px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="footer">
        SIGEPEI — {{ $data['pei']['codigo'] }} | {{ $data['pei']['aluno']['nome'] }} • Gerado em {{ $data['gerado_em'] }}
    </div>

    <div class="header">
        <div class="header-top">Sistema de Gestão de Planos Educacionais Individualizados (SIGEPEI)</div>
        <h1>{{ $data['titulo_relatorio'] }}</h1>
        @if(!empty($data['subtitulo_relatorio']))
            <div class="subtitle">{{ $data['subtitulo_relatorio'] }}</div>
        @endif
        <div class="meta">
            Código: <strong>{{ $data['pei']['codigo'] }}</strong> • Status: <strong>{{ $data['pei']['status'] ?: 'Ativo' }}</strong> • Ano Letivo: <strong>{{ $data['pei']['ano_letivo'] }}</strong> • Emitido em {{ $data['gerado_em'] }}
        </div>
    </div>

    {{-- dados gerais do pei --}}
    <div class="section-title">Dados Gerais do PEI</div>
    <table>
        <tr>
            <td class="label-cell">Discente</td>
            <td style="width: 32%;">{{ $data['pei']['aluno']['nome'] }}</td>
            <td class="label-cell" style="width: 18%;">Matrícula</td>
            <td style="width: 26%;">{{ $data['pei']['aluno']['matricula'] }}</td>
        </tr>
        <tr>
            <td class="label-cell">Curso / Turma</td>
            <td>{{ $data['pei']['turma'] }}</td>
            <td class="label-cell">Data de Nascimento</td>
            <td>{{ $data['pei']['aluno']['data_nascimento'] }}</td>
        </tr>
        <tr>
            <td class="label-cell">Data de Elaboração</td>
            <td>{{ $data['pei']['data_elaboracao'] }}</td>
            <td class="label-cell">Idade na Elaboração</td>
            <td>{{ $data['pei']['idade_na_elaboracao'] }}</td>
        </tr>
        <tr>
            <td class="label-cell">Diagnóstico / Deficiências</td>
            <td colspan="3">
                {!! nl2br(e($data['pei']['diagnostico'])) !!}
                @if(!empty($data['pei']['aluno']['deficiencias']))
                    <div style="margin-top: 4px;">
                        @foreach($data['pei']['aluno']['deficiencias'] as $def)
                            <span class="badge">{{ $def }}</span>
                        @endforeach
                    </div>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label-cell">Responsáveis pela Elaboração</td>
            <td colspan="3">
                @if(!empty($data['pei']['responsaveis_ficha_inicial']))
                    {{ implode(', ', $data['pei']['responsaveis_ficha_inicial']) }}
                @else
                    <span class="muted">Nenhum responsável registrado.</span>
                @endif
            </td>
        </tr>
    </table>

    {{-- 1. ficha do aluno --}}
    @if($data['incluir']['ficha_aluno'])
        <div class="section-title">Ficha do Aluno</div>
        <table>
            <tr>
                <td class="label-cell" style="width: 34%;">O que gosta de fazer no tempo livre?</td>
                <td>{!! nl2br(e($data['pei']['ficha_aluno']['tempo_livre'])) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">Gosta de praticar esporte?</td>
                <td>{!! nl2br(e($data['pei']['ficha_aluno']['pratica_esporte'])) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">Características do discente</td>
                <td>{!! nl2br(e($data['pei']['ficha_aluno']['caracteristicas_discente'])) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">O aluno é organizado? Realiza a entrega de atividades avaliativas dentro do prazo estipulado pelo professor?</td>
                <td>{!! nl2br(e($data['pei']['ficha_aluno']['organizacao_e_prazos'])) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">Apresenta dificuldade na interação social?</td>
                <td>{!! nl2br(e($data['pei']['ficha_aluno']['dificuldade_interacao_social'])) !!}</td>
            </tr>
            <tr>
                <td class="label-cell">Sugestões de recursos ou estratégias para auxiliar na aprendizagem</td>
                <td>{!! nl2br(e($data['pei']['ficha_aluno']['sugestoes_recursos_estrategias'])) !!}</td>
            </tr>
        </table>
    @endif

    {{-- 2. ficha do pei --}}
    @if($data['incluir']['ficha_pei'])
        <div class="section-title">Ficha do PEI — Identificações e Intervenções</div>
        <table>
            @php
                $blocosIdent = [
                    'Suas Habilidades' => $data['identificacoes']['habilidades'],
                    'Suas Dificuldades' => $data['identificacoes']['dificuldades'],
                    'Dificultadores internos do campus' => $data['identificacoes']['dificultadores_internos'],
                    'Facilitadores internos do campus' => $data['identificacoes']['facilitadores_internos'],
                    'Dificultadores externos ao campus' => $data['identificacoes']['dificultadores_externos'],
                    'Facilitadores externos ao campus' => $data['identificacoes']['facilitadores_externos'],
                ];
            @endphp
            @foreach($blocosIdent as $titulo => $itens)
                <tr>
                    <td class="label-cell" style="width: 32%;">{{ $titulo }}</td>
                    <td>
                        @if(!empty($itens))
                            <ul class="clean-list">
                                @foreach($itens as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="muted">Nenhum item registrado.</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>

        <div class="subsection-title">Possibilidades de Intervenção</div>
        <table class="table-header-green">
            <thead>
                <tr>
                    <th style="width: 33%;">Demandas</th>
                    <th style="width: 40%;">Encaminhamento(s) / Solução(ões) / Estratégia(s)</th>
                    <th style="width: 27%;">Responsável(is)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['intervencoes'] as $interv)
                    <tr>
                        <td>{!! nl2br(e($interv['demanda'] ?: '-')) !!}</td>
                        <td>{!! nl2br(e($interv['encaminhamento_estrategia'] ?: '-')) !!}</td>
                        <td>{!! nl2br(e($interv['responsavel'] ?: '-')) !!}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="muted">Nenhuma intervenção registrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- 3. nai (trimestres) --}}
    @if($data['incluir']['nai'])
        <div class="section-title">Acompanhamento do NAI (Trimestres)</div>
        @foreach($data['nai_trimestres'] as $triNum => $tri)
            <div class="subsection-title">{{ $triNum }}º Trimestre</div>
            <div class="box">
                <strong>Responsáveis:</strong>
                @if(!empty($tri['responsaveis']))
                    {{ implode(', ', $tri['responsaveis']) }}
                @else
                    <span class="muted">Nenhum responsável registrado para este trimestre.</span>
                @endif
            </div>
            <table class="table-header-green">
                <thead>
                    <tr>
                        <th style="width: 20%;">Objetivos</th>
                        <th style="width: 25%;">Recursos / Ações</th>
                        <th style="width: 13%;">Tempo</th>
                        <th style="width: 21%;">Avaliação / Resultados</th>
                        <th style="width: 21%;">Sugestões</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tri['atividades'] as $atv)
                        <tr>
                            <td>{!! nl2br(e($atv['objetivos'] ?: '-')) !!}</td>
                            <td>{!! nl2br(e($atv['recursos_acoes'] ?: '-')) !!}</td>
                            <td>{!! nl2br(e($atv['tempo'] ?: '-')) !!}</td>
                            <td>{!! nl2br(e($atv['avaliacao_resultados'] ?: '-')) !!}</td>
                            <td>{!! nl2br(e($atv['sugestoes_aproveitamento'] ?: '-')) !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted">Nenhuma ação/atividade registrada pelo NAI para o {{ $triNum }}º Trimestre.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    @endif

    {{-- 4. equipes de apoio --}}
    @foreach($data['equipes_apoio'] as $apoio)
        <div class="section-title">Equipe de Apoio: {{ $apoio['nome'] }}</div>
        @foreach($apoio['trimestres'] as $triNum => $tri)
            <div class="subsection-title">{{ $triNum }}º Trimestre</div>
            <div class="box">
                <strong>Responsáveis:</strong>
                @if(!empty($tri['responsaveis']))
                    {{ implode(', ', $tri['responsaveis']) }}
                @else
                    <span class="muted">Nenhum responsável registrado para este trimestre.</span>
                @endif
            </div>
            <table class="table-header-green">
                <thead>
                    <tr>
                        <th style="width: 20%;">Objetivos</th>
                        <th style="width: 25%;">Recursos / Ações</th>
                        <th style="width: 13%;">Tempo</th>
                        <th style="width: 21%;">Avaliação / Resultados</th>
                        <th style="width: 21%;">Sugestões</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tri['atividades'] as $atv)
                        <tr>
                            <td>{!! nl2br(e($atv['objetivos'] ?: '-')) !!}</td>
                            <td>{!! nl2br(e($atv['recursos_acoes'] ?: '-')) !!}</td>
                            <td>{!! nl2br(e($atv['tempo'] ?: '-')) !!}</td>
                            <td>{!! nl2br(e($atv['avaliacao_resultados'] ?: '-')) !!}</td>
                            <td>{!! nl2br(e($atv['sugestoes_aproveitamento'] ?: '-')) !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted">Nenhuma ação/atividade registrada por {{ $apoio['nome'] }} para o {{ $triNum }}º Trimestre.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    @endforeach

    {{-- 5. disciplinas --}}
    @foreach($data['disciplinas'] as $disc)
        <div class="section-title">Disciplina: [{{ $disc['codigo'] }}] {{ $disc['nome'] }}</div>
        <div class="box">
            <strong>Professor(es):</strong>
            @if(!empty($disc['professores']))
                {{ implode(', ', $disc['professores']) }}
            @else
                <span class="muted">Não informado</span>
            @endif
        </div>

        @foreach($disc['trimestres'] as $triNum => $tri)
            <div class="subsection-title">{{ $triNum }}º Trimestre</div>

            @if(!$tri['tem_registros'])
                <p class="muted">Nenhum planejamento ou avaliação registrado para este trimestre.</p>
                @continue
            @endif

            @if(!empty($tri['grau_autonomia']))
                <div class="box">
                    <strong>Grau de autonomia para realizar as atividades:</strong>
                    @foreach($tri['grau_autonomia'] as $aut)
                        <span class="badge">{{ $aut }}</span>
                    @endforeach
                </div>
            @endif

            @foreach($tri['planejamentos'] as $pIdx => $plan)
                <table class="avoid-break">
                    <thead>
                        <tr>
                            <th colspan="2">Planejamento Pedagógico #{{ $pIdx + 1 }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="label-cell" style="width: 30%;">Objetivos Acadêmicos</td>
                            <td>{!! nl2br(e($plan['objetivos'] ?: '-')) !!}</td>
                        </tr>
                        <tr>
                            <td class="label-cell">Conteúdos Curriculares Priorizados</td>
                            <td>{!! nl2br(e($plan['conteudos'] ?: '-')) !!}</td>
                        </tr>
                        @if(!empty($plan['estrategias_ensino']) || !empty($plan['estrategias_ensino_outros']))
                            <tr>
                                <td class="label-cell">Estratégias de Ensino</td>
                                <td>
                                    @if(!empty($plan['estrategias_ensino']))
                                        <ul class="clean-list">
                                            @foreach($plan['estrategias_ensino'] as $est)
                                                <li>{{ $est }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if(!empty($plan['estrategias_ensino_outros']))
                                        <div><strong>Outras:</strong> {{ $plan['estrategias_ensino_outros'] }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        @if(!empty($plan['estrategias_avaliacao']) || !empty($plan['estrategias_avaliacao_outros']))
                            <tr>
                                <td class="label-cell">Estratégias e Condições de Avaliação</td>
                                <td>
                                    @if(!empty($plan['estrategias_avaliacao']))
                                        <ul class="clean-list">
                                            @foreach($plan['estrategias_avaliacao'] as $est)
                                                <li>{{ $est }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if(!empty($plan['estrategias_avaliacao_outros']))
                                        <div><strong>Outras:</strong> {{ $plan['estrategias_avaliacao_outros'] }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        @if(empty($plan['estrategias_ensino']) && empty($plan['estrategias_ensino_outros']) && empty($plan['estrategias_avaliacao']) && empty($plan['estrategias_avaliacao_outros']) && !empty($plan['metodologias_recursos']))
                            <tr>
                                <td class="label-cell">Metodologias / Estratégias / Recursos</td>
                                <td>{!! nl2br(e($plan['metodologias_recursos'])) !!}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="label-cell">Tempo / Prazo</td>
                            <td>{{ $plan['tempo'] ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label-cell">Avaliação de Desempenho / Resultados</td>
                            <td>
                                @if(!empty($plan['avaliacao_desempenho']) || !empty($plan['avaliacao_desempenho_outros']))
                                    @if(!empty($plan['avaliacao_desempenho']))
                                        <ul class="clean-list">
                                            @foreach($plan['avaliacao_desempenho'] as $des)
                                                <li>{{ $des }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if(!empty($plan['avaliacao_desempenho_outros']))
                                        <div><strong>Outras observações:</strong> {!! nl2br(e($plan['avaliacao_desempenho_outros'])) !!}</div>
                                    @endif
                                @elseif(!empty($plan['avaliacao_resultados']))
                                    {!! nl2br(e($plan['avaliacao_resultados'])) !!}
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label-cell">Sugestões para Melhor Aproveitamento</td>
                            <td>{!! nl2br(e($plan['sugestoes_aproveitamento'] ?: '-')) !!}</td>
                        </tr>
                    </tbody>
                </table>
            @endforeach

            @php
                $adotados = array_values(array_filter($tri['avaliacoes'], fn($a) => $a['adotado']));
            @endphp
            <div style="font-weight: bold; font-size: 9px; margin-top: 6px; margin-bottom: 4px; color: #334155;">
                Instrumentos de Avaliação Adotados
            </div>
            @if(!empty($adotados))
                <table class="table-header-green avoid-break">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Instrumento</th>
                            <th style="width: 20%;">1º Valor / Nota</th>
                            <th style="width: 20%;">2º Valor / Nota</th>
                            <th style="width: 20%;">3º Valor / Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adotados as $av)
                            <tr>
                                <td>{{ $av['label'] }}</td>
                                <td>{{ ($av['valor_1'] !== '' || $av['nota_1'] !== '') ? 'Valor: ' . ($av['valor_1'] ?: '-') . ' | Nota: ' . ($av['nota_1'] ?: '-') : '-' }}</td>
                                <td>{{ ($av['valor_2'] !== '' || $av['nota_2'] !== '') ? 'Valor: ' . ($av['valor_2'] ?: '-') . ' | Nota: ' . ($av['nota_2'] ?: '-') : '-' }}</td>
                                <td>{{ ($av['valor_3'] !== '' || $av['nota_3'] !== '') ? 'Valor: ' . ($av['valor_3'] ?: '-') . ' | Nota: ' . ($av['nota_3'] ?: '-') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="muted">Nenhum instrumento de avaliação marcado como adotado neste trimestre.</p>
            @endif
        @endforeach
    @endforeach

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $pdf->page_text(500, 815, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 8, [0.39, 0.45, 0.55]);
        }
    </script>
</body>
</html>

