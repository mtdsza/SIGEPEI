<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Deficiencia;
use App\Models\Pei;
use App\Models\PeiApoio;
use App\Models\PeiApoioAtividade;
use App\Models\PeiApoioResponsavel;
use App\Models\PeiDisciplina;
use App\Models\PeiNaiAtividade;
use App\Models\Turma;
use App\Models\TurmaDisciplina;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * exibe o painel principal com indicadores, pendências e métricas de acompanhamento.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $anoAtual = (int) date('Y');
        $mesAtual = (int) date('n');

        // define o trimestre vigente com base no mês atual do calendário
        $trimestreVigente = match (true) {
            $mesAtual <= 4 => 1,
            $mesAtual <= 8 => 2,
            default => 3,
        };

        // obtém o trimestre solicitado na requisição ou adota o vigente como padrão
        $trimestreSelecionado = (int) $request->input('trimestre', $trimestreVigente);
        if (!in_array($trimestreSelecionado, [1, 2, 3])) {
            $trimestreSelecionado = $trimestreVigente;
        }

        // recupera os peis ativos do ano letivo corrente
        $peisAtivos = Pei::with(['aluno.deficiencias', 'turma', 'disciplinasTrimestres.planejamentos', 'disciplinasTrimestres.avaliacoes', 'naiAtividades', 'apoios.atividades', 'apoios.responsaveis', 'responsaveis'])
            ->where('is_active', true)
            ->where('ano_letivo', $anoAtual)
            ->get();

        // busca peis mais recentes caso o ano corrente ainda não possua registros
        if ($peisAtivos->isEmpty()) {
            $peisAtivos = Pei::with(['aluno.deficiencias', 'turma', 'disciplinasTrimestres.planejamentos', 'disciplinasTrimestres.avaliacoes', 'naiAtividades', 'apoios.atividades', 'apoios.responsaveis', 'responsaveis'])
                ->where('is_active', true)
                ->latest('ano_letivo')
                ->take(50)
                ->get();
        }

        // identifica pendências atribuídas diretamente ao usuário logado
        $minhasPendencias = collect();
        $todasPendencias = collect();

        // verifica pendências de planejamento e avaliação nas disciplinas do docente
        $turmasDisciplinasUsuario = TurmaDisciplina::whereHas('professores', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })->with(['turma', 'disciplina'])->get();

        // função auxiliar para empacotar dados prévios de disciplina para o modal de preenchimento rápido
        $mapearDadosEdicao = function ($registroDisc) {
            $tiposPadrao = ['prova_escrita', 'trabalhos', 'prova_pratica', 'outros', 'prova_oral'];
            $avaliacoesMapeadas = [];
            foreach ($tiposPadrao as $tipoKey) {
                $av = $registroDisc?->avaliacoes->where('tipo', $tipoKey)->first();
                $avaliacoesMapeadas[] = [
                    'tipo' => $tipoKey,
                    'adotado' => (bool) ($av?->adotado ?? false),
                    'valor_1' => $av?->valor_1 ?? '',
                    'nota_1' => $av?->nota_1 ?? '',
                    'valor_2' => $av?->valor_2 ?? '',
                    'nota_2' => $av?->nota_2 ?? '',
                    'valor_3' => $av?->valor_3 ?? '',
                    'nota_3' => $av?->nota_3 ?? '',
                ];
            }

            $planejamentosMapeados = $registroDisc ? $registroDisc->planejamentos->map(function ($p) {
                return [
                    'id' => $p->id,
                    'objetivos' => $p->objetivos,
                    'conteudos' => $p->conteudos,
                    'metodologias_recursos' => $p->metodologias_recursos,
                    'estrategias_ensino' => $p->estrategias_ensino ?? [],
                    'estrategias_ensino_outros' => $p->estrategias_ensino_outros ?? '',
                    'estrategias_avaliacao' => $p->estrategias_avaliacao ?? [],
                    'estrategias_avaliacao_outros' => $p->estrategias_avaliacao_outros ?? '',
                    'tempo' => $p->tempo,
                    'tempo_opcao' => $p->tempo_opcao ?? '',
                    'tempo_outro' => $p->tempo_outro ?? '',
                    'avaliacao_resultados' => $p->avaliacao_resultados,
                    'avaliacao_desempenho' => $p->avaliacao_desempenho ?? [],
                    'avaliacao_desempenho_outros' => $p->avaliacao_desempenho_outros ?? '',
                    'sugestoes_aproveitamento' => $p->sugestoes_aproveitamento,
                ];
            })->values()->toArray() : [];

            return [
                'grau_autonomia' => $registroDisc?->grau_autonomia ?? [],
                'especifique_outros' => $registroDisc?->especifique_outros ?? '',
                'planejamentos' => $planejamentosMapeados,
                'avaliacoes' => $avaliacoesMapeadas,
            ];
        };

        foreach ($turmasDisciplinasUsuario as $td) {
            $peisDaTurma = $peisAtivos->where('turma_id', $td->turma_id);

            foreach ($peisDaTurma as $pei) {
                $registroDisc = $pei->disciplinasTrimestres
                    ->where('disciplina_id', $td->disciplina_id)
                    ->where('trimestre', $trimestreSelecionado)
                    ->first();

                $temPlanejamento = $registroDisc && $registroDisc->planejamentos->isNotEmpty();
                $temAvaliacao = $registroDisc && $registroDisc->avaliacoes->isNotEmpty();

                if (!$temPlanejamento || !$temAvaliacao) {
                    $motivo = match (true) {
                        !$temPlanejamento && !$temAvaliacao => 'Planejamento e avaliações não preenchidos',
                        !$temPlanejamento => 'Planejamento pedagógico pendente',
                        default => 'Instrumentos de avaliação pendentes',
                    };

                    $minhasPendencias->push([
                        'id' => "disc_{$pei->id}_{$td->disciplina_id}_{$trimestreSelecionado}",
                        'tipo' => 'disciplina',
                        'tipo_label' => 'Disciplina',
                        'pei_id' => $pei->id,
                        'pei_codigo' => $pei->codigo,
                        'aluno_id' => $pei->aluno_id,
                        'aluno_nome' => $pei->aluno?->nome ?? 'Estudante',
                        'aluno_foto_url' => $pei->aluno?->foto_url,
                        'aluno_matricula' => $pei->aluno?->matricula,
                        'deficiencias' => $pei->aluno?->deficiencias->pluck('nome')->values()->toArray() ?? [],
                        'turma_nome' => $pei->turma?->nome_curso ?? 'Turma',
                        'disciplina_id' => $td->disciplina_id,
                        'disciplina_codigo' => $td->disciplina?->codigo,
                        'atribuicao' => $td->disciplina?->nome ?? 'Disciplina',
                        'trimestre' => $trimestreSelecionado,
                        'is_trimestre_vigente' => $trimestreSelecionado === $trimestreVigente,
                        'detalhe' => $motivo,
                        'urgencia' => ($trimestreSelecionado === $trimestreVigente) ? 'alta' : 'media',
                        'dados_edicao' => $mapearDadosEdicao($registroDisc),
                    ]);
                }
            }
        }

        // verifica pendências do nai para os peis sob responsabilidade do usuário
        foreach ($peisAtivos as $pei) {
            $respNaiDoTrimestre = $pei->responsaveis->where('secao', "nai_{$trimestreSelecionado}");
            
            // verifica atribuição do usuário no 1º trimestre ou na ficha inicial caso o trimestre não tenha responsável explícito
            $isResponsavelNai = $respNaiDoTrimestre->isNotEmpty()
                ? $respNaiDoTrimestre->contains('user_id', $user->id)
                : ($user->cargo === 'NAI' && $pei->responsaveis->whereIn('secao', ['nai_1', 'ficha_inicial'])->contains('user_id', $user->id));

            $temNaiNoTrimestre = $pei->naiAtividades->where('trimestre', $trimestreSelecionado)->isNotEmpty();

            if ($isResponsavelNai && !$temNaiNoTrimestre) {
                $minhasPendencias->push([
                    'id' => "nai_{$pei->id}_{$trimestreSelecionado}",
                    'tipo' => 'nai',
                    'tipo_label' => 'NAI',
                    'pei_id' => $pei->id,
                    'pei_codigo' => $pei->codigo,
                    'aluno_id' => $pei->aluno_id,
                    'aluno_nome' => $pei->aluno?->nome ?? 'Estudante',
                    'aluno_foto_url' => $pei->aluno?->foto_url,
                    'turma_nome' => $pei->turma?->nome_curso ?? 'Turma',
                    'atribuicao' => 'Acompanhamento NAI',
                    'trimestre' => $trimestreSelecionado,
                    'is_trimestre_vigente' => $trimestreSelecionado === $trimestreVigente,
                    'detalhe' => 'Ações do NAI não registradas neste trimestre',
                    'urgencia' => ($trimestreSelecionado === $trimestreVigente) ? 'alta' : 'media',
                ]);
            }
        }

        // verifica pendências nas equipes de apoio vinculadas ao usuário no trimestre
        $apoiosVinculados = PeiApoioResponsavel::where('user_id', $user->id)
            ->where('trimestre', $trimestreSelecionado)
            ->with('apoio.pei.aluno', 'apoio.pei.turma', 'apoio.atividades')
            ->get();

        foreach ($apoiosVinculados as $ar) {
            $apoio = $ar->apoio;
            if ($apoio && $apoio->pei && $apoio->pei->is_active) {
                $temAtividade = $apoio->atividades->where('trimestre', $trimestreSelecionado)->isNotEmpty();
                if (!$temAtividade) {
                    $minhasPendencias->push([
                        'id' => "apoio_{$apoio->id}_{$trimestreSelecionado}",
                        'tipo' => 'equipe_apoio',
                        'tipo_label' => 'Equipe de Apoio',
                        'pei_id' => $apoio->pei_id,
                        'pei_codigo' => $apoio->pei->codigo,
                        'aluno_id' => $apoio->pei->aluno_id,
                        'aluno_nome' => $apoio->pei->aluno?->nome ?? 'Estudante',
                        'aluno_foto_url' => $apoio->pei->aluno?->foto_url,
                        'turma_nome' => $apoio->pei->turma?->nome_curso ?? 'Turma',
                        'atribuicao' => $apoio->nome,
                        'trimestre' => $trimestreSelecionado,
                        'is_trimestre_vigente' => $trimestreSelecionado === $trimestreVigente,
                        'detalhe' => "Ações da equipe '{$apoio->nome}' não preenchidas",
                        'urgencia' => ($trimestreSelecionado === $trimestreVigente) ? 'alta' : 'media',
                    ]);
                }
            }
        }

        // compila todas as pendências globais para visualização da gestão e nai
        if ($user->is_admin || $user->cargo === 'NAI') {
            foreach ($peisAtivos as $pei) {
                // verifica se existem ações do nai pendentes de preenchimento
                if ($pei->naiAtividades->where('trimestre', $trimestreSelecionado)->isEmpty()) {
                    $respNai = $pei->responsaveis->where('secao', "nai_{$trimestreSelecionado}")->map->nome_exibicao->join(', ');
                    $todasPendencias->push([
                        'id' => "inst_nai_{$pei->id}_{$trimestreSelecionado}",
                        'tipo_label' => 'NAI',
                        'pei_id' => $pei->id,
                        'pei_codigo' => $pei->codigo,
                        'aluno_nome' => $pei->aluno?->nome ?? 'Estudante',
                        'aluno_foto_url' => $pei->aluno?->foto_url,
                        'turma_nome' => $pei->turma?->nome_curso ?? 'Turma',
                        'atribuicao' => 'Acompanhamento NAI',
                        'responsaveis' => $respNai ?: 'Nenhum responsável atribuído',
                        'detalhe' => 'Ações do NAI não registradas',
                    ]);
                }

                // valida se o planejamento e as avaliações das disciplinas foram concluídos
                $turmaDiscs = TurmaDisciplina::where('turma_id', $pei->turma_id)->with('disciplina', 'professores')->get();
                foreach ($turmaDiscs as $td) {
                    $registroDisc = $pei->disciplinasTrimestres
                        ->where('disciplina_id', $td->disciplina_id)
                        ->where('trimestre', $trimestreSelecionado)
                        ->first();

                    $temPlanejamento = $registroDisc && $registroDisc->planejamentos->isNotEmpty();
                    $temAvaliacao = $registroDisc && $registroDisc->avaliacoes->isNotEmpty();

                    if (!$temPlanejamento || !$temAvaliacao) {
                        $profs = $td->professores->pluck('name')->join(', ');
                        $todasPendencias->push([
                            'id' => "inst_disc_{$pei->id}_{$td->disciplina_id}_{$trimestreSelecionado}",
                            'tipo' => 'disciplina',
                            'tipo_label' => 'Disciplina',
                            'pei_id' => $pei->id,
                            'pei_codigo' => $pei->codigo,
                            'aluno_id' => $pei->aluno_id,
                            'aluno_nome' => $pei->aluno?->nome ?? 'Estudante',
                            'aluno_foto_url' => $pei->aluno?->foto_url,
                            'aluno_matricula' => $pei->aluno?->matricula,
                            'deficiencias' => $pei->aluno?->deficiencias->pluck('nome')->values()->toArray() ?? [],
                            'turma_nome' => $pei->turma?->nome_curso ?? 'Turma',
                            'disciplina_id' => $td->disciplina_id,
                            'disciplina_codigo' => $td->disciplina?->codigo,
                            'atribuicao' => $td->disciplina?->nome ?? 'Disciplina',
                            'trimestre' => $trimestreSelecionado,
                            'responsaveis' => $profs ?: 'Sem professor associado',
                            'detalhe' => !$temPlanejamento && !$temAvaliacao ? 'Planejamento e avaliações pendentes' : (!$temPlanejamento ? 'Planejamento pendente' : 'Avaliações pendentes'),
                            'dados_edicao' => $mapearDadosEdicao($registroDisc),
                        ]);
                    }
                }
            }
        }

        // calcula o progresso geral e a taxa de preenchimento dos peis no trimestre
        $totalPeis = $peisAtivos->count();
        $totalAlunos = Aluno::where('is_active', true)->count();
        $totalTurmas = Turma::where('is_active', true)->count();

        // quantifica itens esperados versus preenchidos para calcular a taxa percentual
        $totalItensEsperados = 0;
        $totalItensPreenchidos = 0;

        foreach ($peisAtivos as $p) {
            // contabiliza preenchimento das ações do nai
            $totalItensEsperados++;
            if ($p->naiAtividades->where('trimestre', $trimestreSelecionado)->isNotEmpty()) {
                $totalItensPreenchidos++;
            }

            // contabiliza preenchimento das disciplinas da turma
            $disciplinasTurmaCount = TurmaDisciplina::where('turma_id', $p->turma_id)->count();
            $totalItensEsperados += $disciplinasTurmaCount;

            $preenchidasCount = $p->disciplinasTrimestres
                ->where('trimestre', $trimestreSelecionado)
                ->filter(fn ($d) => $d->planejamentos->isNotEmpty() || $d->avaliacoes->isNotEmpty())
                ->count();

            $totalItensPreenchidos += $preenchidasCount;
        }

        $taxaConclusao = $totalItensEsperados > 0
            ? round(($totalItensPreenchidos / $totalItensEsperados) * 100)
            : 100;

        // turmas e disciplinas lecionadas pelo usuário
        $turmasDisciplinasUsuario = TurmaDisciplina::whereHas('professores', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })->with('disciplina')->get();

        $turmasDocenteIds = $turmasDisciplinasUsuario->pluck('turma_id')->unique()->toArray();

        // filtra os peis nos quais o usuário possui atribuição de responsabilidade em qualquer parte
        $peisUsuario = $peisAtivos->filter(function (Pei $pei) use ($user, $turmasDocenteIds) {
            $isRespPei = $pei->responsaveis->contains('user_id', $user->id);
            $isRespApoio = $pei->apoios->pluck('responsaveis')->flatten()->contains('user_id', $user->id);
            $isProfessorTurma = in_array($pei->turma_id, $turmasDocenteIds);

            return $isRespPei || $isRespApoio || $isProfessorTurma;
        });

        // monta a listagem de peis para acompanhamento trimestral com status baseado nas atribuições do usuário logado
        $peisRecentes = $peisUsuario->map(function (Pei $pei) use ($user, $turmasDisciplinasUsuario, $trimestreVigente) {
            $disciplinasDoUsuarioNaTurma = $turmasDisciplinasUsuario
                ->where('turma_id', $pei->turma_id)
                ->pluck('disciplina_id')
                ->toArray();

            $statusTrimestres = [];
            for ($t = 1; $t <= 3; $t++) {
                $totalObrigacoes = 0;
                $obrigacoesConcluidas = 0;
                $obrigacoesParciais = 0;

                // verifica disciplinas atribuídas ao docente
                foreach ($disciplinasDoUsuarioNaTurma as $discId) {
                    $totalObrigacoes++;
                    $regDisc = $pei->disciplinasTrimestres
                        ->where('disciplina_id', $discId)
                        ->where('trimestre', $t)
                        ->first();

                    $temPlan = $regDisc && $regDisc->planejamentos->isNotEmpty();
                    $temAv = $regDisc && $regDisc->avaliacoes->isNotEmpty();

                    if ($temPlan && $temAv) {
                        $obrigacoesConcluidas++;
                    } elseif ($temPlan || $temAv) {
                        $obrigacoesParciais++;
                    }
                }

                // verifica acompanhamento do nai se o usuário for responsável
                $respNai = $pei->responsaveis->where('secao', "nai_{$t}");
                $isRespNai = $respNai->isNotEmpty()
                    ? $respNai->contains('user_id', $user->id)
                    : ($user->cargo === 'NAI' && $pei->responsaveis->whereIn('secao', ['nai_1', 'ficha_inicial'])->contains('user_id', $user->id));

                if ($isRespNai) {
                    $totalObrigacoes++;
                    $temNai = $pei->naiAtividades->where('trimestre', $t)->isNotEmpty();
                    if ($temNai) {
                        $obrigacoesConcluidas++;
                    }
                }

                // verifica equipes de apoio onde o usuário é responsável no trimestre
                foreach ($pei->apoios as $apoio) {
                    $isRespApoio = $apoio->responsaveis->where('trimestre', $t)->contains('user_id', $user->id);
                    if ($isRespApoio) {
                        $totalObrigacoes++;
                        $temApoioAtiv = $apoio->atividades->where('trimestre', $t)->isNotEmpty();
                        if ($temApoioAtiv) {
                            $obrigacoesConcluidas++;
                        }
                    }
                }

                // define o status do trimestre para o usuário autenticado
                if ($totalObrigacoes === 0) {
                    $status = 'completo';
                } elseif ($obrigacoesConcluidas === $totalObrigacoes) {
                    $status = 'completo';
                } elseif ($obrigacoesConcluidas > 0 || $obrigacoesParciais > 0) {
                    $status = 'andamento';
                } else {
                    $status = 'pendente';
                }

                $statusTrimestres[$t] = [
                    'trimestre' => $t,
                    'status' => $status,
                    'is_vigente' => $t === $trimestreVigente,
                    'disciplinas_preenchidas' => $obrigacoesConcluidas,
                    'disciplinas_total' => $totalObrigacoes,
                ];
            }

            return [
                'id' => $pei->id,
                'codigo' => $pei->codigo,
                'aluno_id' => $pei->aluno_id,
                'aluno_nome' => $pei->aluno?->nome ?? 'Estudante',
                'aluno_foto_url' => $pei->aluno?->foto_url,
                'aluno_matricula' => $pei->aluno?->matricula ?? '-',
                'turma_nome' => $pei->turma?->nome_curso ?? 'Turma',
                'ano_letivo' => $pei->ano_letivo,
                'diagnostico' => $pei->diagnostico ?? 'Não informado',
                'status_trimestres' => $statusTrimestres,
            ];
        })->values();

        // agrega dados para os gráficos de distribuição por turmas e deficiências
        $distribuicaoTurmas = Turma::where('is_active', true)
            ->withCount(['alunos' => function ($q) {
                $q->where('aluno_turma.is_atual', true);
            }])
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'nome' => $t->nome_curso,
                'alunos_count' => $t->alunos_count,
            ]);

        $distribuicaoDeficiencias = Deficiencia::has('alunos')
            ->withCount('alunos')
            ->orderByDesc('alunos_count')
            ->take(6)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'codigo' => $d->codigo,
                'nome' => $d->nome,
                'total' => $d->alunos_count,
            ]);

        return Inertia::render('Dashboard', [
            'metrics' => [
                'peis_count' => $totalPeis,
                'alunos_count' => $totalAlunos,
                'turmas_count' => $totalTurmas,
                'ano_atual' => $anoAtual,
                'trimestre_vigente' => $trimestreVigente,
                'trimestre_selecionado' => $trimestreSelecionado,
                'taxa_conclusao' => $taxaConclusao,
                'minhas_pendencias_count' => $minhasPendencias->count(),
                'todas_pendencias_count' => $todasPendencias->count(),
            ],
            'minhasPendencias' => $minhasPendencias->values(),
            'todasPendencias' => $todasPendencias->values(),
            'peisRecentes' => $peisRecentes,
            'distribuicaoTurmas' => $distribuicaoTurmas,
            'distribuicaoDeficiencias' => $distribuicaoDeficiencias,
            'usuario' => [
                'id' => $user->id,
                'name' => $user->name,
                'cargo' => $user->cargo,
                'is_admin' => (bool) $user->is_admin,
            ],
        ]);
    }
}
