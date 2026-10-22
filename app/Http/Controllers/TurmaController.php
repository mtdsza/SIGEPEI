<?php

namespace App\Http\Controllers;

use App\Models\Disciplina;
use App\Models\Turma;
use App\Models\TurmaDisciplina;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TurmaController extends Controller
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
        $turmas = Turma::withCount(['turmaDisciplinas', 'alunos'])
            ->orderBy('ano_letivo', 'desc')
            ->orderBy('nome_curso', 'asc')
            ->get()
            ->map(function (Turma $t) {
                return [
                    'id' => $t->id,
                    'nome' => $t->nome_curso,
                    'ano' => $t->ano_letivo,
                    'disciplinas_qtd' => "{$t->turma_disciplinas_count} disciplina(s)",
                    'alunos_qtd' => "{$t->alunos_count} aluno(s)",
                    'ativo' => (bool) $t->is_active,
                ];
            });

        return Inertia::render('Turmas/Index', [
            'turmas' => $turmas,
        ]);
    }

    public function create(): Response
    {
        $this->ensureAdminOrNai();
        $disciplinas = Disciplina::where('is_active', true)
            ->select('id', 'codigo', 'nome')
            ->orderBy('nome', 'asc')
            ->get();

        $professores = User::where('is_active', true)
            ->select('id', 'name', 'email', 'cargo')
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('Turmas/Create', [
            'disciplinasDisponiveis' => $disciplinas,
            'professoresDisponiveis' => $professores,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $validated = $request->validate([
            'nome_curso' => ['required', 'string', 'max:255'],
            'ano_letivo' => ['required', 'integer'],
            'disciplinas' => ['nullable', 'array'],
            'disciplinas.*.disciplina_id' => ['required', 'exists:disciplinas,id'],
            'disciplinas.*.professores' => ['nullable', 'array'],
            'disciplinas.*.professores.*.user_id' => ['required', 'exists:users,id'],
            'disciplinas.*.professores.*.is_substituto' => ['boolean'],
        ]);

        $turma = Turma::create([
            'nome_curso' => $validated['nome_curso'],
            'ano_letivo' => $validated['ano_letivo'],
            'is_active' => true,
        ]);

        if (!empty($validated['disciplinas'])) {
            foreach ($validated['disciplinas'] as $discData) {
                $turmaDisciplina = TurmaDisciplina::create([
                    'turma_id' => $turma->id,
                    'disciplina_id' => $discData['disciplina_id'],
                ]);

                if (!empty($discData['professores'])) {
                    foreach ($discData['professores'] as $prof) {
                        $turmaDisciplina->professores()->attach($prof['user_id'], [
                            'is_substituto' => (bool) ($prof['is_substituto'] ?? false),
                        ]);
                    }
                }
            }
        }

        // registra a criação da turma na trilha de auditoria
        AuditService::log(
            'Turmas',
            'Criação',
            "Criou a turma {$turma->nome_curso} ({$turma->ano_letivo})",
            $turma,
            null,
            [
                'nome_curso' => $turma->nome_curso,
                'ano_letivo' => $turma->ano_letivo,
                'disciplinas_qtd' => count($validated['disciplinas'] ?? []),
            ]
        );

        return redirect()->route('turmas.show', $turma->id)->with('success', 'Turma criada com sucesso.');
    }

    public function show(Turma $turma): Response
    {
        $turma->load([
            'turmaDisciplinas.disciplina',
            'turmaDisciplinas.professores',
            'alunos' => function ($q) {
                $q->orderBy('nome', 'asc');
            },
        ]);

        $disciplinasFormatadas = $turma->turmaDisciplinas->map(function (TurmaDisciplina $td) {
            return [
                'id' => $td->id,
                'disciplina_id' => $td->disciplina->id,
                'codigo' => $td->disciplina->codigo,
                'nome' => $td->disciplina->nome,
                'professores' => $td->professores->map(function (User $p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'email' => $p->email,
                        'is_substituto' => (bool) $p->pivot->is_substituto,
                    ];
                }),
            ];
        });

        $alunosFormatados = $turma->alunos->map(function ($aluno) {
            return [
                'id' => $aluno->id,
                'matricula' => $aluno->matricula,
                'nome' => $aluno->nome,
                'foto_url' => $aluno->foto_url,
                'is_atual' => (bool) $aluno->pivot->is_atual,
            ];
        });

        return Inertia::render('Turmas/Show', [
            'turma' => [
                'id' => $turma->id,
                'nome_curso' => $turma->nome_curso,
                'ano_letivo' => $turma->ano_letivo,
                'is_active' => (bool) $turma->is_active,
                'disciplinas' => $disciplinasFormatadas,
                'alunos' => $alunosFormatados,
            ],
        ]);
    }

    public function edit(Turma $turma): Response
    {
        $this->ensureAdminOrNai();
        $turma->load(['turmaDisciplinas.professores', 'turmaDisciplinas.disciplina']);

        $disciplinas = Disciplina::where('is_active', true)
            ->select('id', 'codigo', 'nome')
            ->orderBy('nome', 'asc')
            ->get();

        $professores = User::where('is_active', true)
            ->select('id', 'name', 'email', 'cargo')
            ->orderBy('name', 'asc')
            ->get();

        $disciplinasTurma = $turma->turmaDisciplinas->map(function (TurmaDisciplina $td) {
            return [
                'disciplina_id' => $td->disciplina_id,
                'professores' => $td->professores->map(function (User $p) {
                    return [
                        'user_id' => $p->id,
                        'is_substituto' => (bool) $p->pivot->is_substituto,
                    ];
                })->toArray(),
            ];
        })->toArray();

        return Inertia::render('Turmas/Edit', [
            'turma' => [
                'id' => $turma->id,
                'nome_curso' => $turma->nome_curso,
                'ano_letivo' => $turma->ano_letivo,
                'disciplinas' => $disciplinasTurma,
            ],
            'disciplinasDisponiveis' => $disciplinas,
            'professoresDisponiveis' => $professores,
        ]);
    }

    public function update(Request $request, Turma $turma): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $validated = $request->validate([
            'nome_curso' => ['required', 'string', 'max:255'],
            'ano_letivo' => ['required', 'integer'],
            'disciplinas' => ['nullable', 'array'],
            'disciplinas.*.disciplina_id' => ['required', 'exists:disciplinas,id'],
            'disciplinas.*.professores' => ['nullable', 'array'],
            'disciplinas.*.professores.*.user_id' => ['required', 'exists:users,id'],
            'disciplinas.*.professores.*.is_substituto' => ['boolean'],
        ]);

        $dadosAnteriores = [
            'nome_curso' => $turma->nome_curso,
            'ano_letivo' => $turma->ano_letivo,
            'disciplinas' => $turma->turmaDisciplinas()->with(['disciplina', 'professores'])->get()->map(function ($td) {
                $profs = $td->professores->map(fn($p) => $p->name . ($p->pivot->is_substituto ? ' (Substituto)' : ''))->join(', ');
                return ($td->disciplina?->nome ?? 'N/A') . ($profs ? " [Docentes: {$profs}]" : '');
            })->values()->toArray(),
        ];

        $turma->update([
            'nome_curso' => $validated['nome_curso'],
            'ano_letivo' => $validated['ano_letivo'],
        ]);

        // recria as associações de disciplinas e docentes da turma
        $turma->turmaDisciplinas()->delete();

        if (!empty($validated['disciplinas'])) {
            foreach ($validated['disciplinas'] as $discData) {
                $turmaDisciplina = TurmaDisciplina::create([
                    'turma_id' => $turma->id,
                    'disciplina_id' => $discData['disciplina_id'],
                ]);

                if (!empty($discData['professores'])) {
                    foreach ($discData['professores'] as $prof) {
                        $turmaDisciplina->professores()->attach($prof['user_id'], [
                            'is_substituto' => (bool) ($prof['is_substituto'] ?? false),
                        ]);
                    }
                }
            }
        }

        $dadosNovos = [
            'nome_curso' => $turma->nome_curso,
            'ano_letivo' => $turma->ano_letivo,
            'disciplinas' => $turma->fresh()->turmaDisciplinas()->with(['disciplina', 'professores'])->get()->map(function ($td) {
                $profs = $td->professores->map(fn($p) => $p->name . ($p->pivot->is_substituto ? ' (Substituto)' : ''))->join(', ');
                return ($td->disciplina?->nome ?? 'N/A') . ($profs ? " [Docentes: {$profs}]" : '');
            })->values()->toArray(),
        ];

        // registra a atualização da turma na trilha de auditoria
        AuditService::log(
            'Turmas',
            'Atualização',
            "Atualizou a grade e os dados da turma {$turma->nome_curso}",
            $turma,
            $dadosAnteriores,
            $dadosNovos
        );

        return redirect()->route('turmas.show', $turma->id)->with('success', 'Turma atualizada com sucesso.');
    }

    public function destroy(Turma $turma): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $turma->is_active = !$turma->is_active;
        $turma->save();

        $statusMsg = $turma->is_active ? 'reativada' : 'inativada';
        $acao = $turma->is_active ? 'Reativação' : 'Inativação';

        // registra a alteração de status da turma na trilha de auditoria
        AuditService::log(
            'Turmas',
            $acao,
            ($turma->is_active ? 'Reativou' : 'Inativou') . " a turma {$turma->nome_curso}",
            $turma,
            ['is_active' => !$turma->is_active],
            ['is_active' => $turma->is_active]
        );

        return redirect()->route('turmas.index')->with('success', "Turma {$statusMsg} com sucesso.");
    }
}
