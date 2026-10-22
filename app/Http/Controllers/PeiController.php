<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\Pei;
use App\Models\PeiApoio;
use App\Models\PeiApoioAtividade;
use App\Models\PeiApoioResponsavel;
use App\Models\PeiDisciplina;
use App\Models\PeiDisciplinaAvaliacao;
use App\Models\PeiDisciplinaPlanejamento;
use App\Models\PeiIdentificacao;
use App\Models\PeiIntervencao;
use App\Models\PeiNaiAtividade;
use App\Models\PeiResponsavel;
use App\Models\Turma;
use App\Models\TurmaDisciplina;
use App\Models\User;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PeiController extends Controller
{
    /**
     * valida se o usuário autenticado possui perfil de administrador ou integrante do nai.
     */
    private function ensureAdminOrNai(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->is_admin && $user->cargo !== 'NAI')) {
            abort(403, 'Acesso restrito à equipe do NAI e administradores.');
        }
    }

    public function index(Request $request): Response
    {
        $peis = Pei::with(['aluno', 'turma'])
            ->orderBy('ano_letivo', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function (Pei $pei) {
                return [
                    'id' => $pei->id,
                    'codigo' => $pei->codigo,
                    'aluno' => $pei->aluno?->nome ?? 'Aluno não vinculado',
                    'turma' => $pei->turma?->nome_curso ?? 'Turma não informada',
                    'ano' => $pei->ano_letivo,
                    'status' => $pei->status,
                    'ativo' => (bool) $pei->is_active,
                ];
            });

        return Inertia::render('Peis/Index', [
            'peis' => $peis,
        ]);
    }

    public function create(): Response
    {
        $this->ensureAdminOrNai();
        $alunos = Aluno::where('is_active', true)
            ->with([
                'deficiencias',
                'turmas',
                'peis' => function ($q) {
                    $q->with(['identificacoes', 'intervencoes', 'responsaveis'])->latest('ano_letivo');
                },
            ])
            ->orderBy('nome', 'asc')
            ->get()
            ->map(function (Aluno $aluno) {
                $turmaAtual = $aluno->turmaAtual();
                $ultimoPei = $aluno->peis->first();

                $ultimoPeiFormatado = null;
                if ($ultimoPei) {
                    $ultimoPeiFormatado = [
                        'id' => $ultimoPei->id,
                        'codigo' => $ultimoPei->codigo,
                        'ano_letivo' => $ultimoPei->ano_letivo,
                        'diagnostico' => $ultimoPei->diagnostico,
                        // ficha do aluno
                        'tempo_livre' => $ultimoPei->tempo_livre ?? '',
                        'pratica_esporte' => $ultimoPei->pratica_esporte ?? '',
                        'caracteristicas_discente' => $ultimoPei->caracteristicas_discente ?? '',
                        'organizacao_e_prazos' => $ultimoPei->organizacao_e_prazos ?? '',
                        'dificuldade_interacao_social' => $ultimoPei->dificuldade_interacao_social ?? '',
                        'sugestoes_recursos_estrategias' => $ultimoPei->sugestoes_recursos_estrategias ?? '',
                        // ficha do pei
                        'responsaveis' => $ultimoPei->responsaveis->where('secao', 'ficha_inicial')->map(fn($r) => [
                            'tipo' => $r->user_id ? 'usuario' : 'externo',
                            'user_id' => $r->user_id ? (string) $r->user_id : '',
                            'nome_externo' => $r->nome_externo ?? '',
                        ])->values()->toArray(),
                        'habilidades' => $ultimoPei->identificacoes->where('tipo', 'habilidade')->pluck('descricao')->values()->toArray(),
                        'dificuldades' => $ultimoPei->identificacoes->where('tipo', 'dificuldade')->pluck('descricao')->values()->toArray(),
                        'dificultadores_internos' => $ultimoPei->identificacoes->where('tipo', 'dificultador_interno')->pluck('descricao')->values()->toArray(),
                        'facilitadores_internos' => $ultimoPei->identificacoes->where('tipo', 'facilitador_interno')->pluck('descricao')->values()->toArray(),
                        'dificultadores_externos' => $ultimoPei->identificacoes->where('tipo', 'dificultador_externo')->pluck('descricao')->values()->toArray(),
                        'facilitadores_externos' => $ultimoPei->identificacoes->where('tipo', 'facilitador_externo')->pluck('descricao')->values()->toArray(),
                        'intervencoes' => $ultimoPei->intervencoes->map(fn($i) => [
                            'demanda' => $i->demanda,
                            'encaminhamento_estrategia' => $i->encaminhamento_estrategia,
                            'responsavel' => $i->responsavel,
                        ])->values()->toArray(),
                    ];
                }

                return [
                    'id' => $aluno->id,
                    'nome' => $aluno->nome,
                    'matricula' => $aluno->matricula,
                    'turma_id' => $turmaAtual?->id,
                    'turma_nome' => $turmaAtual?->nome_curso,
                    'ano_letivo' => $turmaAtual ? $turmaAtual->pivot->ano_letivo : date('Y'),
                    'deficiencias_texto' => $aluno->deficiencias->map(fn($d) => "[{$d->codigo}] {$d->nome}")->join(', '),
                    'ultimo_pei' => $ultimoPeiFormatado,
                ];
            });

        $turmas = Turma::where('is_active', true)
            ->select('id', 'nome_curso', 'ano_letivo')
            ->orderBy('ano_letivo', 'desc')
            ->orderBy('nome_curso', 'asc')
            ->get();

        $usuarios = User::where('is_active', true)
            ->select('id', 'name', 'email', 'cargo')
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('Peis/Create', [
            'alunosDisponiveis' => $alunos,
            'turmasDisponiveis' => $turmas,
            'usuariosDisponiveis' => $usuarios,
            'hoje' => Carbon::now()->format('Y-m-d'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminOrNai();

        $validated = $request->validate([
            'aluno_id' => ['required', 'exists:alunos,id'],
            'turma_id' => ['nullable', 'exists:turmas,id'],
            'ano_letivo' => ['required', 'integer'],
            'data_elaboracao' => ['required', 'date'],
            'diagnostico' => ['nullable', 'string'],
            // ficha do aluno
            'tempo_livre' => ['nullable', 'string'],
            'pratica_esporte' => ['nullable', 'string'],
            'caracteristicas_discente' => ['nullable', 'string'],
            'organizacao_e_prazos' => ['nullable', 'string'],
            'dificuldade_interacao_social' => ['nullable', 'string'],
            'sugestoes_recursos_estrategias' => ['nullable', 'string'],
            // responsáveis pela elaboração
            'responsaveis' => ['nullable', 'array'],
            'responsaveis.*.user_id' => ['nullable', 'exists:users,id'],
            'responsaveis.*.nome_externo' => ['nullable', 'string', 'max:255'],
            // identificações
            'habilidades' => ['nullable', 'array'],
            'habilidades.*' => ['nullable', 'string'],
            'dificuldades' => ['nullable', 'array'],
            'dificuldades.*' => ['nullable', 'string'],
            'dificultadores_internos' => ['nullable', 'array'],
            'dificultadores_internos.*' => ['nullable', 'string'],
            'facilitadores_internos' => ['nullable', 'array'],
            'facilitadores_internos.*' => ['nullable', 'string'],
            'dificultadores_externos' => ['nullable', 'array'],
            'dificultadores_externos.*' => ['nullable', 'string'],
            'facilitadores_externos' => ['nullable', 'array'],
            'facilitadores_externos.*' => ['nullable', 'string'],
            // intervenções pedagógicas
            'intervencoes' => ['nullable', 'array'],
            'intervencoes.*.demanda' => ['nullable', 'string'],
            'intervencoes.*.encaminhamento_estrategia' => ['nullable', 'string'],
            'intervencoes.*.responsavel' => ['nullable', 'string'],
        ]);

        $ano = $validated['ano_letivo'];
        $countAno = Pei::where('ano_letivo', $ano)->count() + 1;
        $codigo = sprintf('PEI-%d-%03d', $ano, $countAno);

        $pei = Pei::create([
            'codigo' => $codigo,
            'aluno_id' => $validated['aluno_id'],
            'turma_id' => $validated['turma_id'] ?? null,
            'ano_letivo' => $ano,
            'data_elaboracao' => $validated['data_elaboracao'],
            'diagnostico' => $validated['diagnostico'] ?? null,
            'tempo_livre' => $validated['tempo_livre'] ?? null,
            'pratica_esporte' => $validated['pratica_esporte'] ?? null,
            'caracteristicas_discente' => $validated['caracteristicas_discente'] ?? null,
            'organizacao_e_prazos' => $validated['organizacao_e_prazos'] ?? null,
            'dificuldade_interacao_social' => $validated['dificuldade_interacao_social'] ?? null,
            'sugestoes_recursos_estrategias' => $validated['sugestoes_recursos_estrategias'] ?? null,
            'status' => 'Ativo',
            'is_active' => true,
        ]);

        if (!empty($validated['responsaveis'])) {
            foreach ($validated['responsaveis'] as $resp) {
                if (!empty($resp['user_id']) || !empty($resp['nome_externo'])) {
                    PeiResponsavel::create([
                        'pei_id' => $pei->id,
                        'user_id' => $resp['user_id'] ?? null,
                        'nome_externo' => $resp['nome_externo'] ?? null,
                        'secao' => 'ficha_inicial',
                    ]);
                }
            }
        }

        $mapTipos = [
            'habilidade' => $validated['habilidades'] ?? [],
            'dificuldade' => $validated['dificuldades'] ?? [],
            'dificultador_interno' => $validated['dificultadores_internos'] ?? [],
            'facilitador_interno' => $validated['facilitadores_internos'] ?? [],
            'dificultador_externo' => $validated['dificultadores_externos'] ?? [],
            'facilitador_externo' => $validated['facilitadores_externos'] ?? [],
        ];

        foreach ($mapTipos as $tipo => $itens) {
            foreach ($itens as $idx => $texto) {
                if (filled($texto)) {
                    PeiIdentificacao::create([
                        'pei_id' => $pei->id,
                        'tipo' => $tipo,
                        'descricao' => trim($texto),
                        'ordem' => $idx,
                    ]);
                }
            }
        }

        if (!empty($validated['intervencoes'])) {
            foreach ($validated['intervencoes'] as $idx => $interv) {
                if (filled($interv['demanda'] ?? null) || filled($interv['encaminhamento_estrategia'] ?? null)) {
                    PeiIntervencao::create([
                        'pei_id' => $pei->id,
                        'demanda' => $interv['demanda'] ?? '',
                        'encaminhamento_estrategia' => $interv['encaminhamento_estrategia'] ?? '',
                        'responsavel' => $interv['responsavel'] ?? '',
                        'ordem' => $idx,
                    ]);
                }
            }
        }

        // registra a criação do pei na trilha de auditoria
        AuditService::log(
            'PEIs',
            'Criação',
            "Elaborou o PEI {$pei->codigo} para o discente " . ($pei->aluno?->nome ?? 'N/A') . " (Ano Letivo: {$pei->ano_letivo})",
            $pei,
            null,
            [
                'codigo' => $pei->codigo,
                'aluno_id' => $pei->aluno_id,
                'turma_id' => $pei->turma_id,
                'ano_letivo' => $pei->ano_letivo,
            ]
        );

        return redirect()->route('peis.show', $pei->id)->with('success', 'PEI criado com sucesso.');
    }

    public function show(Pei $pei): Response
    {
        $pei->load([
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

        $user = Auth::user();

        $usuariosDisponiveis = User::where('is_active', true)
            ->select('id', 'name', 'email', 'cargo')
            ->orderBy('name', 'asc')
            ->get();

        $identificacoesAgrupadas = [
            'habilidades' => $pei->identificacoes->where('tipo', 'habilidade')->pluck('descricao')->values(),
            'dificuldades' => $pei->identificacoes->where('tipo', 'dificuldade')->pluck('descricao')->values(),
            'dificultadores_internos' => $pei->identificacoes->where('tipo', 'dificultador_interno')->pluck('descricao')->values(),
            'facilitadores_internos' => $pei->identificacoes->where('tipo', 'facilitador_interno')->pluck('descricao')->values(),
            'dificultadores_externos' => $pei->identificacoes->where('tipo', 'dificultador_externo')->pluck('descricao')->values(),
            'facilitadores_externos' => $pei->identificacoes->where('tipo', 'facilitador_externo')->pluck('descricao')->values(),
        ];

        $responsaveisFichaInicial = $pei->responsaveis->where('secao', 'ficha_inicial')->map(function ($r) {
            return [
                'id' => $r->id,
                'user_id' => $r->user_id,
                'nome' => $r->nome_exibicao,
                'nome_externo' => $r->nome_externo,
            ];
        })->values();

        // histórico de ações e responsáveis do nai nos três trimestres
        $naiTrimestres = [];
        for ($t = 1; $t <= 3; $t++) {
            $respNai = $pei->responsaveis->where('secao', "nai_{$t}")->map(function ($r) {
                return [
                    'id' => $r->id,
                    'user_id' => $r->user_id,
                    'nome' => $r->nome_exibicao,
                    'nome_externo' => $r->nome_externo,
                ];
            })->values();

            $atividadesNai = $pei->naiAtividades->where('trimestre', $t)->map(function ($atv) {
                return [
                    'id' => $atv->id,
                    'objetivos' => $atv->objetivos,
                    'recursos_acoes' => $atv->recursos_acoes,
                    'tempo' => $atv->tempo,
                    'avaliacao_resultados' => $atv->avaliacao_resultados,
                    'sugestoes_aproveitamento' => $atv->sugestoes_aproveitamento,
                ];
            })->values();

            $naiTrimestres[$t] = [
                'trimestre' => $t,
                'responsaveis' => $respNai,
                'atividades' => $atividadesNai,
            ];
        }

        // acompanhamento das equipes de apoio nos três trimestres
        $equipesApoio = $pei->apoios->map(function (PeiApoio $apoio) {
            $trimestres = [];
            for ($t = 1; $t <= 3; $t++) {
                $respApoio = $apoio->responsaveis->where('trimestre', $t)->map(function (PeiApoioResponsavel $r) {
                    return [
                        'id' => $r->id,
                        'user_id' => $r->user_id,
                        'nome' => $r->nome_exibicao,
                        'nome_externo' => $r->nome_externo,
                    ];
                })->values();

                $atividadesApoio = $apoio->atividades->where('trimestre', $t)->map(function (PeiApoioAtividade $atv) {
                    return [
                        'id' => $atv->id,
                        'objetivos' => $atv->objetivos,
                        'recursos_acoes' => $atv->recursos_acoes,
                        'tempo' => $atv->tempo,
                        'avaliacao_resultados' => $atv->avaliacao_resultados,
                        'sugestoes_aproveitamento' => $atv->sugestoes_aproveitamento,
                    ];
                })->values();

                $trimestres[$t] = [
                    'trimestre' => $t,
                    'responsaveis' => $respApoio,
                    'atividades' => $atividadesApoio,
                ];
            }

            return [
                'id' => $apoio->id,
                'nome' => $apoio->nome,
                'trimestres' => $trimestres,
            ];
        })->values();

        // disciplinas vinculadas à turma do aluno do pei
        $turmaDisciplinas = TurmaDisciplina::with(['disciplina', 'professores'])
            ->where('turma_id', $pei->turma_id)
            ->get();

        $disciplinasData = $turmaDisciplinas->map(function (TurmaDisciplina $td) use ($pei, $user) {
            $disciplina = $td->disciplina;
            if (!$disciplina) {
                return null;
            }

            $professores = $td->professores->map(function ($prof) {
                return [
                    'id' => $prof->id,
                    'name' => $prof->name,
                    'email' => $prof->email,
                    'is_substituto' => (bool) $prof->pivot->is_substituto,
                ];
            });

            $podeEditar = (bool) ($user->is_admin || $user->cargo === 'NAI' || $td->professores->contains('id', $user->id));

            $trimestres = [];
            for ($t = 1; $t <= 3; $t++) {
                $peiDisc = $pei->disciplinasTrimestres
                    ->where('disciplina_id', $disciplina->id)
                    ->where('trimestre', $t)
                    ->first();

                $planejamentos = $peiDisc ? $peiDisc->planejamentos->map(function ($p) {
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
                })->values() : [];

                $tiposPadrao = ['prova_escrita', 'trabalhos', 'prova_pratica', 'outros', 'prova_oral'];
                $avaliacoesMapeadas = [];
                foreach ($tiposPadrao as $tipoKey) {
                    $av = $peiDisc?->avaliacoes->where('tipo', $tipoKey)->first();
                    $avaliacoesMapeadas[$tipoKey] = [
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

                $trimestres[$t] = [
                    'trimestre' => $t,
                    'planejamentos' => $planejamentos,
                    'grau_autonomia' => $peiDisc?->grau_autonomia ?? [],
                    'especifique_outros' => $peiDisc?->especifique_outros ?? '',
                    'avaliacoes' => $avaliacoesMapeadas,
                ];
            }

            return [
                'id' => $disciplina->id,
                'codigo' => $disciplina->codigo,
                'nome' => $disciplina->nome,
                'professores' => $professores,
                'pode_editar' => $podeEditar,
                'trimestres' => $trimestres,
            ];
        })->filter()->values();

        $idadeNaElaboracao = null;
        if ($pei->aluno?->data_nascimento && $pei->data_elaboracao) {
            $nasc = Carbon::parse($pei->aluno->data_nascimento);
            $elab = Carbon::parse($pei->data_elaboracao);
            $idadeNaElaboracao = ((int) $nasc->diffInYears($elab)) . ' anos';
        }

        return Inertia::render('Peis/Show', [
            'pei' => [
                'id' => $pei->id,
                'codigo' => $pei->codigo,
                'aluno' => [
                    'id' => $pei->aluno?->id,
                    'nome' => $pei->aluno?->nome,
                    'matricula' => $pei->aluno?->matricula,
                    'data_nascimento' => $pei->aluno?->data_nascimento ? Carbon::parse($pei->aluno->data_nascimento)->format('d/m/Y') : '-',
                    'deficiencias' => $pei->aluno?->deficiencias->map(fn($d) => "[{$d->codigo}] {$d->nome}"),
                ],
                'turma' => [
                    'id' => $pei->turma?->id,
                    'nome_curso' => $pei->turma?->nome_curso,
                ],
                'ano_letivo' => $pei->ano_letivo,
                'data_elaboracao' => $pei->data_elaboracao ? Carbon::parse($pei->data_elaboracao)->format('d/m/Y') : '-',
                'data_elaboracao_iso' => $pei->data_elaboracao ? Carbon::parse($pei->data_elaboracao)->format('Y-m-d') : '',
                'idade_na_elaboracao' => $idadeNaElaboracao ?? '-',
                'diagnostico' => $pei->diagnostico,
                'status' => $pei->status,
                'is_active' => (bool) $pei->is_active,
                // ficha do aluno
                'ficha_aluno' => [
                    'tempo_livre' => $pei->tempo_livre ?? '',
                    'pratica_esporte' => $pei->pratica_esporte ?? '',
                    'caracteristicas_discente' => $pei->caracteristicas_discente ?? '',
                    'organizacao_e_prazos' => $pei->organizacao_e_prazos ?? '',
                    'dificuldade_interacao_social' => $pei->dificuldade_interacao_social ?? '',
                    'sugestoes_recursos_estrategias' => $pei->sugestoes_recursos_estrategias ?? '',
                ],
                'responsaveis_ficha_inicial' => $responsaveisFichaInicial,
                'identificacoes' => $identificacoesAgrupadas,
                'intervencoes' => $pei->intervencoes->map(fn($i) => [
                    'id' => $i->id,
                    'demanda' => $i->demanda,
                    'encaminhamento_estrategia' => $i->encaminhamento_estrategia,
                    'responsavel' => $i->responsavel,
                ])->values(),
                'nai_trimestres' => $naiTrimestres,
                'equipes_apoio' => $equipesApoio,
                'disciplinas' => $disciplinasData,
            ],
            'usuariosDisponiveis' => $usuariosDisponiveis,
        ]);
    }

    public function updateFichaAluno(Request $request, Pei $pei): RedirectResponse
    {
        $this->ensureAdminOrNai();

        $validated = $request->validate([
            'tempo_livre' => ['nullable', 'string'],
            'pratica_esporte' => ['nullable', 'string'],
            'caracteristicas_discente' => ['nullable', 'string'],
            'organizacao_e_prazos' => ['nullable', 'string'],
            'dificuldade_interacao_social' => ['nullable', 'string'],
            'sugestoes_recursos_estrategias' => ['nullable', 'string'],
        ]);

        $dadosAnteriores = [
            'tempo_livre' => $pei->tempo_livre,
            'pratica_esporte' => $pei->pratica_esporte,
            'caracteristicas_discente' => $pei->caracteristicas_discente,
            'organizacao_e_prazos' => $pei->organizacao_e_prazos,
            'dificuldade_interacao_social' => $pei->dificuldade_interacao_social,
            'sugestoes_recursos_estrategias' => $pei->sugestoes_recursos_estrategias,
        ];

        $pei->update($validated);

        // registra a atualização da ficha do aluno na trilha de auditoria
        AuditService::log(
            'PEIs',
            'Atualização',
            "Atualizou a Ficha do Aluno no PEI {$pei->codigo} (" . ($pei->aluno?->nome ?? 'N/A') . ")",
            $pei,
            $dadosAnteriores,
            $validated
        );

        return redirect()->route('peis.show', $pei->id)->with('success', 'Ficha do Aluno atualizada com sucesso.');
    }

    public function updateFichaPei(Request $request, Pei $pei): RedirectResponse
    {
        $this->ensureAdminOrNai();

        $validated = $request->validate([
            'diagnostico' => ['nullable', 'string'],
            'responsaveis' => ['nullable', 'array'],
            'responsaveis.*.tipo' => ['nullable', 'string'],
            'responsaveis.*.user_id' => ['nullable', 'exists:users,id'],
            'responsaveis.*.nome_externo' => ['nullable', 'string', 'max:255'],
            'habilidades' => ['nullable', 'array'],
            'dificuldades' => ['nullable', 'array'],
            'dificultadores_internos' => ['nullable', 'array'],
            'facilitadores_internos' => ['nullable', 'array'],
            'dificultadores_externos' => ['nullable', 'array'],
            'facilitadores_externos' => ['nullable', 'array'],
            'intervencoes' => ['nullable', 'array'],
            'intervencoes.*.demanda' => ['nullable', 'string'],
            'intervencoes.*.encaminhamento_estrategia' => ['nullable', 'string'],
            'intervencoes.*.responsavel' => ['nullable', 'string'],
        ]);

        $dadosAnteriores = [
            'diagnostico' => $pei->diagnostico,
            'responsaveis' => $pei->responsaveis()->where('secao', 'ficha_inicial')->get()->map(fn($r) => $r->user?->name ?? $r->nome_externo)->values()->toArray(),
            'habilidades' => $pei->identificacoes()->where('tipo', 'habilidade')->pluck('descricao')->values()->toArray(),
            'dificuldades' => $pei->identificacoes()->where('tipo', 'dificuldade')->pluck('descricao')->values()->toArray(),
            'dificultadores_internos' => $pei->identificacoes()->where('tipo', 'dificultador_interno')->pluck('descricao')->values()->toArray(),
            'facilitadores_internos' => $pei->identificacoes()->where('tipo', 'facilitador_interno')->pluck('descricao')->values()->toArray(),
            'dificultadores_externos' => $pei->identificacoes()->where('tipo', 'dificultador_externo')->pluck('descricao')->values()->toArray(),
            'facilitadores_externos' => $pei->identificacoes()->where('tipo', 'facilitador_externo')->pluck('descricao')->values()->toArray(),
            'intervencoes' => $pei->intervencoes()->get()->map(fn($i) => "{$i->demanda} | {$i->encaminhamento_estrategia} | {$i->responsavel}")->values()->toArray(),
        ];

        $pei->update([
            'diagnostico' => $validated['diagnostico'] ?? null,
        ]);

        // atualiza os responsáveis da ficha do pei
        $pei->responsaveis()->where('secao', 'ficha_inicial')->delete();
        if (!empty($validated['responsaveis'])) {
            foreach ($validated['responsaveis'] as $resp) {
                if (!empty($resp['user_id']) || !empty($resp['nome_externo'])) {
                    $isUser = ($resp['tipo'] ?? 'usuario') === 'usuario';
                    $pei->responsaveis()->create([
                        'user_id' => $isUser ? ($resp['user_id'] ?: null) : null,
                        'nome_externo' => !$isUser ? ($resp['nome_externo'] ?: null) : null,
                        'secao' => 'ficha_inicial',
                    ]);
                }
            }
        }

        // atualiza as identificações de habilidades e dificuldades
        $pei->identificacoes()->delete();
        $tipos = [
            'habilidade' => 'habilidades',
            'dificuldade' => 'dificuldades',
            'dificultador_interno' => 'dificultadores_internos',
            'facilitador_interno' => 'facilitadores_internos',
            'dificultador_externo' => 'dificultadores_externos',
            'facilitador_externo' => 'facilitadores_externos',
        ];

        foreach ($tipos as $tipoEnum => $inputKey) {
            if (!empty($validated[$inputKey])) {
                foreach ($validated[$inputKey] as $index => $texto) {
                    if (filled($texto)) {
                        $pei->identificacoes()->create([
                            'tipo' => $tipoEnum,
                            'descricao' => trim($texto),
                            'ordem' => $index,
                        ]);
                    }
                }
            }
        }

        // atualiza as intervenções pedagógicas propostas
        $pei->intervencoes()->delete();
        if (!empty($validated['intervencoes'])) {
            foreach ($validated['intervencoes'] as $index => $int) {
                if (filled($int['demanda'] ?? null) || filled($int['encaminhamento_estrategia'] ?? null)) {
                    $pei->intervencoes()->create([
                        'demanda' => $int['demanda'] ?? '',
                        'encaminhamento_estrategia' => $int['encaminhamento_estrategia'] ?? '',
                        'responsavel' => $int['responsavel'] ?? '',
                        'ordem' => $index,
                    ]);
                }
            }
        }

        $dadosNovos = [
            'diagnostico' => $validated['diagnostico'] ?? null,
            'responsaveis' => array_values(array_filter(array_map(function($r) {
                if (!empty($r['user_id'])) {
                    return User::find($r['user_id'])?->name;
                }
                return $r['nome_externo'] ?? null;
            }, $validated['responsaveis'] ?? []))),
            'habilidades' => array_values(array_filter($validated['habilidades'] ?? [])),
            'dificuldades' => array_values(array_filter($validated['dificuldades'] ?? [])),
            'dificultadores_internos' => array_values(array_filter($validated['dificultadores_internos'] ?? [])),
            'facilitadores_internos' => array_values(array_filter($validated['facilitadores_internos'] ?? [])),
            'dificultadores_externos' => array_values(array_filter($validated['dificultadores_externos'] ?? [])),
            'facilitadores_externos' => array_values(array_filter($validated['facilitadores_externos'] ?? [])),
            'intervencoes' => array_values(array_map(fn($i) => "{$i['demanda']} | {$i['encaminhamento_estrategia']} | {$i['responsavel']}", $validated['intervencoes'] ?? [])),
        ];

        // registra a atualização da ficha do pei na trilha de auditoria
        AuditService::log(
            'PEIs',
            'Atualização',
            "Atualizou a Ficha do PEI {$pei->codigo} (Identificações e Intervenções)",
            $pei,
            $dadosAnteriores,
            $dadosNovos
        );

        return redirect()->route('peis.show', $pei->id)->with('success', 'Ficha do PEI atualizada com sucesso.');
    }

    public function updateNai(Request $request, Pei $pei): RedirectResponse
    {
        $this->ensureAdminOrNai();

        $validated = $request->validate([
            'trimestre' => ['required', 'integer', 'in:1,2,3'],
            'responsaveis' => ['nullable', 'array'],
            'responsaveis.*.user_id' => ['nullable', 'exists:users,id'],
            'responsaveis.*.nome_externo' => ['nullable', 'string', 'max:255'],
            'atividades' => ['nullable', 'array'],
            'atividades.*.objetivos' => ['required', 'string'],
            'atividades.*.recursos_acoes' => ['required', 'string'],
            'atividades.*.tempo' => ['required', 'string'],
            'atividades.*.avaliacao_resultados' => ['nullable', 'string'],
            'atividades.*.sugestoes_aproveitamento' => ['nullable', 'string'],
        ]);

        $trimestre = $validated['trimestre'];
        $secao = "nai_{$trimestre}";

        $dadosAnteriores = [
            'trimestre' => $trimestre,
            'responsaveis' => $pei->responsaveis()->where('secao', $secao)->get()->map(fn($r) => $r->user?->name ?? $r->nome_externo)->values()->toArray(),
            'atividades' => $pei->naiAtividades()->where('trimestre', $trimestre)->get()->map(fn($a) => "Objetivos: {$a->objetivos} | Recursos: {$a->recursos_acoes} | Tempo: {$a->tempo}")->values()->toArray(),
        ];

        PeiResponsavel::where('pei_id', $pei->id)->where('secao', $secao)->delete();
        if (!empty($validated['responsaveis'])) {
            foreach ($validated['responsaveis'] as $resp) {
                if (!empty($resp['user_id']) || !empty($resp['nome_externo'])) {
                    PeiResponsavel::create([
                        'pei_id' => $pei->id,
                        'user_id' => $resp['user_id'] ?? null,
                        'nome_externo' => $resp['nome_externo'] ?? null,
                        'secao' => $secao,
                    ]);
                }
            }
        }

        PeiNaiAtividade::where('pei_id', $pei->id)->where('trimestre', $trimestre)->delete();
        if (!empty($validated['atividades'])) {
            foreach ($validated['atividades'] as $idx => $atv) {
                PeiNaiAtividade::create([
                    'pei_id' => $pei->id,
                    'trimestre' => $trimestre,
                    'objetivos' => $atv['objetivos'],
                    'recursos_acoes' => $atv['recursos_acoes'],
                    'tempo' => $atv['tempo'],
                    'avaliacao_resultados' => $atv['avaliacao_resultados'] ?? null,
                    'sugestoes_aproveitamento' => $atv['sugestoes_aproveitamento'] ?? null,
                    'ordem' => $idx,
                ]);
            }
        }

        $dadosNovos = [
            'trimestre' => $trimestre,
            'responsaveis' => array_values(array_filter(array_map(function($r) {
                if (!empty($r['user_id'])) {
                    return User::find($r['user_id'])?->name;
                }
                return $r['nome_externo'] ?? null;
            }, $validated['responsaveis'] ?? []))),
            'atividades' => array_values(array_map(fn($a) => "Objetivos: {$a['objetivos']} | Recursos: {$a['recursos_acoes']} | Tempo: {$a['tempo']}", $validated['atividades'] ?? [])),
        ];

        // registra a atualização das ações do nai na trilha de auditoria
        AuditService::log(
            'PEIs',
            'Atualização',
            "Atualizou as Ações do NAI para o {$trimestre}º Trimestre no PEI {$pei->codigo}",
            $pei,
            $dadosAnteriores,
            $dadosNovos
        );

        return redirect()->route('peis.show', $pei->id)->with('success', "Ações do NAI para o {$trimestre}º Trimestre salvas com sucesso.");
    }

    // cadastra uma nova equipe de apoio vinculada ao pei
    public function storeApoio(Request $request, Pei $pei): RedirectResponse
    {
        $this->ensureAdminOrNai();

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
        ]);

        $count = $pei->apoios()->count();

        $apoio = PeiApoio::create([
            'pei_id' => $pei->id,
            'nome' => $validated['nome'],
            'ordem' => $count,
        ]);

        // registra a inclusão da equipe de apoio na trilha de auditoria
        AuditService::log(
            'PEIs',
            'Criação',
            "Adicionou a Equipe de Apoio '{$apoio->nome}' no PEI {$pei->codigo}",
            $pei
        );

        return redirect()->route('peis.show', $pei->id)->with('success', "Nova aba para a equipe '{$apoio->nome}' adicionada ao PEI.");
    }

    // atualiza o nome identificador da equipe de apoio
    public function updateApoio(Request $request, Pei $pei, PeiApoio $apoio): RedirectResponse
    {
        $this->ensureAdminOrNai();

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
        ]);

        $apoio->update($validated);

        return redirect()->route('peis.show', $pei->id)->with('success', "Nome da equipe atualizado para '{$apoio->nome}'.");
    }

    // atualiza responsáveis e atividades da equipe de apoio no trimestre informado
    public function updateApoioTrimestre(Request $request, Pei $pei, PeiApoio $apoio): RedirectResponse
    {
        $user = Auth::user();
        $trimestre = (int) $request->input('trimestre', 1);
        $isResponsavel = $apoio->responsaveis()->where('trimestre', $trimestre)->where('user_id', $user?->id)->exists();
        if (!$user || (!$user->is_admin && $user->cargo !== 'NAI' && !$isResponsavel)) {
            abort(403, 'Você não possui permissão para editar esta equipe de apoio.');
        }

        $validated = $request->validate([
            'trimestre' => ['required', 'integer', 'in:1,2,3'],
            'responsaveis' => ['nullable', 'array'],
            'responsaveis.*.user_id' => ['nullable', 'exists:users,id'],
            'responsaveis.*.nome_externo' => ['nullable', 'string', 'max:255'],
            'atividades' => ['nullable', 'array'],
            'atividades.*.objetivos' => ['required', 'string'],
            'atividades.*.recursos_acoes' => ['required', 'string'],
            'atividades.*.tempo' => ['required', 'string'],
            'atividades.*.avaliacao_resultados' => ['nullable', 'string'],
            'atividades.*.sugestoes_aproveitamento' => ['nullable', 'string'],
        ]);

        $trimestre = $validated['trimestre'];

        $dadosAnteriores = [
            'equipe' => $apoio->nome,
            'trimestre' => $trimestre,
            'responsaveis' => $apoio->responsaveis()->where('trimestre', $trimestre)->get()->map(fn($r) => $r->user?->name ?? $r->nome_externo)->values()->toArray(),
            'atividades' => $apoio->atividades()->where('trimestre', $trimestre)->get()->map(fn($a) => "Objetivos: {$a->objetivos} | Recursos: {$a->recursos_acoes} | Tempo: {$a->tempo}")->values()->toArray(),
        ];

        // atualiza a listagem de responsáveis no trimestre
        PeiApoioResponsavel::where('pei_apoio_id', $apoio->id)->where('trimestre', $trimestre)->delete();
        if (!empty($validated['responsaveis'])) {
            foreach ($validated['responsaveis'] as $resp) {
                if (!empty($resp['user_id']) || !empty($resp['nome_externo'])) {
                    PeiApoioResponsavel::create([
                        'pei_apoio_id' => $apoio->id,
                        'trimestre' => $trimestre,
                        'user_id' => $resp['user_id'] ?? null,
                        'nome_externo' => $resp['nome_externo'] ?? null,
                    ]);
                }
            }
        }

        // atualiza as atividades e resultados da equipe no trimestre
        PeiApoioAtividade::where('pei_apoio_id', $apoio->id)->where('trimestre', $trimestre)->delete();
        if (!empty($validated['atividades'])) {
            foreach ($validated['atividades'] as $idx => $atv) {
                PeiApoioAtividade::create([
                    'pei_apoio_id' => $apoio->id,
                    'trimestre' => $trimestre,
                    'objetivos' => $atv['objetivos'],
                    'recursos_acoes' => $atv['recursos_acoes'],
                    'tempo' => $atv['tempo'],
                    'avaliacao_resultados' => $atv['avaliacao_resultados'] ?? null,
                    'sugestoes_aproveitamento' => $atv['sugestoes_aproveitamento'] ?? null,
                    'ordem' => $idx,
                ]);
            }
        }

        $dadosNovos = [
            'equipe' => $apoio->nome,
            'trimestre' => $trimestre,
            'responsaveis' => array_values(array_filter(array_map(function($r) {
                if (!empty($r['user_id'])) {
                    return User::find($r['user_id'])?->name;
                }
                return $r['nome_externo'] ?? null;
            }, $validated['responsaveis'] ?? []))),
            'atividades' => array_values(array_map(fn($a) => "Objetivos: {$a['objetivos']} | Recursos: {$a['recursos_acoes']} | Tempo: {$a['tempo']}", $validated['atividades'] ?? [])),
        ];

        // registra a atualização da equipe de apoio na trilha de auditoria
        AuditService::log(
            'PEIs',
            'Atualização',
            "Atualizou as ações da Equipe de Apoio '{$apoio->nome}' para o {$trimestre}º Trimestre no PEI {$pei->codigo}",
            $pei,
            $dadosAnteriores,
            $dadosNovos
        );

        return redirect()->route('peis.show', $pei->id)->with('success', "Ações de {$apoio->nome} para o {$trimestre}º Trimestre salvas com sucesso.");
    }

    // remove a equipe de apoio e seus registros vinculados
    public function destroyApoio(Pei $pei, PeiApoio $apoio): RedirectResponse
    {
        $this->ensureAdminOrNai();

        $nome = $apoio->nome;
        $apoio->delete();
        return redirect()->route('peis.show', $pei->id)->with('success', "Equipe de apoio '{$nome}' e sua aba foram removidas com sucesso.");
    }

    // atualiza o planejamento e as avaliações da disciplina no trimestre
    public function updateDisciplinaTrimestre(Request $request, Pei $pei, Disciplina $disciplina): RedirectResponse
    {
        $user = Auth::user();

        // valida se o usuário tem permissão para editar a disciplina
        $td = TurmaDisciplina::where('turma_id', $pei->turma_id)
            ->where('disciplina_id', $disciplina->id)
            ->with('professores')
            ->first();

        $podeEditar = (bool) ($user->is_admin || $user->cargo === 'NAI' || ($td && $td->professores->contains('id', $user->id)));
        if (!$podeEditar) {
            abort(403, 'Você não possui permissão para editar o planejamento desta disciplina.');
        }

        $validated = $request->validate([
            'trimestre' => ['required', 'integer', 'in:1,2,3'],
            'grau_autonomia' => ['nullable', 'array'],
            'grau_autonomia.*' => ['string'],
            'especifique_outros' => ['nullable', 'string', 'max:255'],
            'planejamentos' => ['nullable', 'array'],
            'planejamentos.*.objetivos' => ['required', 'string'],
            'planejamentos.*.conteudos' => ['required', 'string'],
            'planejamentos.*.metodologias_recursos' => ['nullable', 'string'],
            'planejamentos.*.estrategias_ensino' => ['nullable', 'array'],
            'planejamentos.*.estrategias_ensino.*' => ['string'],
            'planejamentos.*.estrategias_ensino_outros' => ['nullable', 'string'],
            'planejamentos.*.estrategias_avaliacao' => ['nullable', 'array'],
            'planejamentos.*.estrategias_avaliacao.*' => ['string'],
            'planejamentos.*.estrategias_avaliacao_outros' => ['nullable', 'string'],
            'planejamentos.*.tempo' => ['nullable', 'string'],
            'planejamentos.*.tempo_opcao' => ['nullable', 'string'],
            'planejamentos.*.tempo_outro' => ['nullable', 'string'],
            'planejamentos.*.avaliacao_resultados' => ['nullable', 'string'],
            'planejamentos.*.avaliacao_desempenho' => ['nullable', 'array'],
            'planejamentos.*.avaliacao_desempenho.*' => ['string'],
            'planejamentos.*.avaliacao_desempenho_outros' => ['nullable', 'string'],
            'planejamentos.*.sugestoes_aproveitamento' => ['nullable', 'string'],
            'avaliacoes' => ['nullable', 'array'],
            'avaliacoes.*.tipo' => ['required', 'string'],
            'avaliacoes.*.adotado' => ['required', 'boolean'],
            'avaliacoes.*.valor_1' => ['nullable', 'string', 'max:50'],
            'avaliacoes.*.nota_1' => ['nullable', 'string', 'max:50'],
            'avaliacoes.*.valor_2' => ['nullable', 'string', 'max:50'],
            'avaliacoes.*.nota_2' => ['nullable', 'string', 'max:50'],
            'avaliacoes.*.valor_3' => ['nullable', 'string', 'max:50'],
            'avaliacoes.*.nota_3' => ['nullable', 'string', 'max:50'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $trimestre = $validated['trimestre'];

        $peiDisciplinaAntigo = PeiDisciplina::with(['planejamentos', 'avaliacoes'])
            ->where('pei_id', $pei->id)
            ->where('disciplina_id', $disciplina->id)
            ->where('trimestre', $trimestre)
            ->first();

        $dadosAnteriores = [
            'disciplina' => $disciplina->nome,
            'trimestre' => $trimestre,
            'grau_autonomia' => $peiDisciplinaAntigo?->grau_autonomia ?? [],
            'especifique_outros' => $peiDisciplinaAntigo?->especifique_outros ?? null,
            'planejamentos' => $peiDisciplinaAntigo ? $peiDisciplinaAntigo->planejamentos->map(fn($p) => "Objetivos: {$p->objetivos} | Conteúdos: {$p->conteudos} | Recursos: {$p->metodologias_recursos}")->values()->toArray() : [],
            'avaliacoes' => $peiDisciplinaAntigo ? $peiDisciplinaAntigo->avaliacoes->map(fn($av) => "Tipo: {$av->tipo} | Adotado: " . ($av->adotado ? 'Sim' : 'Não') . " | N1: " . ($av->nota_1 ?? '-') . " | N2: " . ($av->nota_2 ?? '-'))->values()->toArray() : [],
        ];

        $peiDisciplina = PeiDisciplina::firstOrCreate([
            'pei_id' => $pei->id,
            'disciplina_id' => $disciplina->id,
            'trimestre' => $trimestre,
        ]);

        $peiDisciplina->update([
            'grau_autonomia' => $validated['grau_autonomia'] ?? [],
            'especifique_outros' => $validated['especifique_outros'] ?? null,
        ]);

        // atualiza os planejamentos pedagógicos da disciplina
        $peiDisciplina->planejamentos()->delete();
        if (!empty($validated['planejamentos'])) {
            foreach ($validated['planejamentos'] as $idx => $plan) {
                // sintetiza metodologias_recursos caso não venha preenchido diretamente
                $metodologiasPartes = [];
                if (!empty($plan['estrategias_ensino'])) {
                    $metodologiasPartes[] = "Estratégias de ensino:\n• " . implode("\n• ", $plan['estrategias_ensino']);
                }
                if (!empty($plan['estrategias_ensino_outros'])) {
                    $metodologiasPartes[] = "Outras estratégias de ensino: {$plan['estrategias_ensino_outros']}";
                }
                if (!empty($plan['estrategias_avaliacao'])) {
                    $metodologiasPartes[] = "Estratégias e condições de avaliação:\n• " . implode("\n• ", $plan['estrategias_avaliacao']);
                }
                if (!empty($plan['estrategias_avaliacao_outros'])) {
                    $metodologiasPartes[] = "Outras estratégias de avaliação: {$plan['estrategias_avaliacao_outros']}";
                }
                $metodologiasFinal = !empty($metodologiasPartes)
                    ? implode("\n\n", $metodologiasPartes)
                    : ($plan['metodologias_recursos'] ?? '');

                $tempoOpcao = $plan['tempo_opcao'] ?? null;
                $tempoOutro = $plan['tempo_outro'] ?? null;
                $tempoFinal = ($tempoOpcao === 'Outro' ? $tempoOutro : $tempoOpcao) ?: ($plan['tempo'] ?? '');

                $avaliacaoPartes = [];
                if (!empty($plan['avaliacao_desempenho'])) {
                    $avaliacaoPartes[] = "• " . implode("\n• ", $plan['avaliacao_desempenho']);
                }
                if (!empty($plan['avaliacao_desempenho_outros'])) {
                    $avaliacaoPartes[] = "Outras observações: {$plan['avaliacao_desempenho_outros']}";
                }
                $avaliacaoResultadosFinal = !empty($avaliacaoPartes)
                    ? implode("\n\n", $avaliacaoPartes)
                    : ($plan['avaliacao_resultados'] ?? null);

                $peiDisciplina->planejamentos()->create([
                    'objetivos' => $plan['objetivos'],
                    'conteudos' => $plan['conteudos'],
                    'metodologias_recursos' => $metodologiasFinal,
                    'estrategias_ensino' => $plan['estrategias_ensino'] ?? null,
                    'estrategias_ensino_outros' => $plan['estrategias_ensino_outros'] ?? null,
                    'estrategias_avaliacao' => $plan['estrategias_avaliacao'] ?? null,
                    'estrategias_avaliacao_outros' => $plan['estrategias_avaliacao_outros'] ?? null,
                    'tempo' => $tempoFinal,
                    'tempo_opcao' => $tempoOpcao,
                    'tempo_outro' => $tempoOutro,
                    'avaliacao_resultados' => $avaliacaoResultadosFinal,
                    'avaliacao_desempenho' => $plan['avaliacao_desempenho'] ?? null,
                    'avaliacao_desempenho_outros' => $plan['avaliacao_desempenho_outros'] ?? null,
                    'sugestoes_aproveitamento' => $plan['sugestoes_aproveitamento'] ?? null,
                    'ordem' => $idx,
                ]);
            }
        }

        // atualiza os instrumentos e notas de avaliação
        $peiDisciplina->avaliacoes()->delete();
        if (!empty($validated['avaliacoes'])) {
            foreach ($validated['avaliacoes'] as $av) {
                $peiDisciplina->avaliacoes()->create([
                    'tipo' => $av['tipo'],
                    'adotado' => (bool) $av['adotado'],
                    'valor_1' => $av['valor_1'] ?? null,
                    'nota_1' => $av['nota_1'] ?? null,
                    'valor_2' => $av['valor_2'] ?? null,
                    'nota_2' => $av['nota_2'] ?? null,
                    'valor_3' => $av['valor_3'] ?? null,
                    'nota_3' => $av['nota_3'] ?? null,
                ]);
            }
        }

        $dadosNovos = [
            'disciplina' => $disciplina->nome,
            'trimestre' => $trimestre,
            'grau_autonomia' => $validated['grau_autonomia'] ?? [],
            'especifique_outros' => $validated['especifique_outros'] ?? null,
            'planejamentos' => array_values(array_map(fn($p) => "Objetivos: {$p['objetivos']} | Conteúdos: {$p['conteudos']} | Recursos: " . ($p['metodologias_recursos'] ?? ''), $validated['planejamentos'] ?? [])),
            'avaliacoes' => array_values(array_map(fn($av) => "Tipo: {$av['tipo']} | Adotado: " . ($av['adotado'] ? 'Sim' : 'Não') . " | N1: " . ($av['nota_1'] ?? '-') . " | N2: " . ($av['nota_2'] ?? '-'), $validated['avaliacoes'] ?? [])),
        ];

        // registra o planejamento e avaliações da disciplina na trilha de auditoria
        AuditService::log(
            'PEIs',
            'Atualização',
            "Atualizou o planejamento e avaliações da disciplina {$disciplina->nome} ({$trimestre}º Trimestre) no PEI {$pei->codigo}",
            $pei,
            $dadosAnteriores,
            $dadosNovos
        );

        $redirectUrl = $validated['redirect_to'] ?? null;
        if ($redirectUrl) {
            return redirect($redirectUrl)->with('success', "Planejamento e avaliações de {$disciplina->nome} ({$trimestre}º Trimestre) salvos com sucesso.");
        }

        return redirect()->route('peis.show', $pei->id)->with('success', "Planejamento e avaliações de {$disciplina->nome} ({$trimestre}º Trimestre) salvos com sucesso.");
    }

    public function destroy(Pei $pei): RedirectResponse
    {
        $this->ensureAdminOrNai();

        $pei->is_active = !$pei->is_active;
        $pei->save();

        $statusMsg = $pei->is_active ? 'reativado' : 'inativado';
        $acao = $pei->is_active ? 'Reativação' : 'Inativação';

        // registra a alteração de status do pei na trilha de auditoria
        AuditService::log(
            'PEIs',
            $acao,
            ($pei->is_active ? 'Reativou' : 'Inativou') . " o PEI {$pei->codigo}",
            $pei,
            ['is_active' => !$pei->is_active],
            ['is_active' => $pei->is_active]
        );

        return redirect()->route('peis.index')->with('success', "PEI {$statusMsg} com sucesso.");
    }
}
