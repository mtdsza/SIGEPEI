<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Deficiencia;
use App\Models\Turma;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlunoController extends Controller
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

    /**
     * disco privado utilizado para armazenar as fotos dos discentes (não exposto publicamente).
     */
    private const FOTO_DISK = 'local';

    /**
     * diretório de armazenamento das fotos de perfil dos discentes.
     */
    private const FOTO_DIR = 'alunos/fotos';

    /**
     * entrega a foto de perfil do aluno apenas para usuários autenticados.
     */
    public function foto(Aluno $aluno): StreamedResponse
    {
        if (!$aluno->foto_path || !Storage::disk(self::FOTO_DISK)->exists($aluno->foto_path)) {
            abort(404);
        }

        return Storage::disk(self::FOTO_DISK)->response($aluno->foto_path, null, [
            'Cache-Control' => 'private, max-age=604800',
        ]);
    }

    /**
     * exibe a listagem de alunos cadastrados com suas turmas e deficiências.
     */
    public function index(Request $request): Response
    {
        $alunos = Aluno::with(['turmas', 'deficiencias'])
            ->orderBy('nome', 'asc')
            ->get()
            ->map(function (Aluno $aluno) {
                $turmaAtual = $aluno->turmaAtual();
                return [
                    'id' => $aluno->id,
                    'nome' => $aluno->nome,
                    'matricula' => $aluno->matricula,
                    'foto_url' => $aluno->foto_url,
                    'data_nasc' => $aluno->data_nascimento ? Carbon::parse($aluno->data_nascimento)->format('d/m/Y') : '-',
                    'turma' => $turmaAtual ? "{$turmaAtual->nome_curso} ({$turmaAtual->pivot->ano_letivo})" : 'Não vinculado',
                    'ativo' => (bool) $aluno->is_active,
                ];
            });

        return Inertia::render('Alunos/Index', [
            'alunos' => $alunos,
        ]);
    }

    /**
     * exibe o formulário de cadastro de novo aluno.
     */
    public function create(): Response
    {
        $this->ensureAdminOrNai();
        $deficiencias = Deficiencia::where('is_active', true)
            ->select('id', 'codigo', 'nome')
            ->orderBy('nome', 'asc')
            ->get();

        $turmas = Turma::where('is_active', true)
            ->select('id', 'nome_curso', 'ano_letivo')
            ->orderBy('ano_letivo', 'desc')
            ->orderBy('nome_curso', 'asc')
            ->get();

        return Inertia::render('Alunos/Create', [
            'deficienciasDisponiveis' => $deficiencias,
            'turmasDisponiveis' => $turmas,
        ]);
    }

    /**
     * salva um novo aluno no banco de dados e vincula suas deficiências e turma inicial.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'matricula' => ['required', 'string', 'max:50', 'unique:alunos,matricula'],
            'data_nascimento' => ['required', 'date', 'before_or_equal:today'],
            'deficiencias' => ['nullable', 'array'],
            'deficiencias.*' => ['exists:deficiencias,id'],
            'turma_id' => ['nullable', 'exists:turmas,id'],
        ]);

        $aluno = DB::transaction(function () use ($validated) {
            $aluno = Aluno::create([
                'nome' => $validated['nome'],
                'matricula' => $validated['matricula'],
                'data_nascimento' => $validated['data_nascimento'],
                'is_active' => true,
            ]);

            if (!empty($validated['deficiencias'])) {
                $aluno->deficiencias()->sync($validated['deficiencias']);
            }

            if (!empty($validated['turma_id'])) {
                $turma = Turma::find($validated['turma_id']);
                $aluno->turmas()->attach($turma->id, [
                    'ano_letivo' => $turma->ano_letivo,
                    'is_atual' => true,
                ]);
            }

            return $aluno;
        });

        // registra o cadastro do aluno na trilha de auditoria
        AuditService::log(
            'Alunos',
            'Criação',
            "Cadastrou o aluno(a) {$aluno->nome} (Matrícula: {$aluno->matricula})",
            $aluno,
            null,
            [
                'nome' => $aluno->nome,
                'matricula' => $aluno->matricula,
                'data_nascimento' => $aluno->data_nascimento?->format('d/m/Y'),
                'deficiencias_qtd' => count($validated['deficiencias'] ?? []),
            ]
        );

        return redirect()->route('alunos.show', $aluno->id)->with('success', 'Aluno cadastrado com sucesso.');
    }

    /**
     * exibe os detalhes completos do aluno, incluindo turmas e histórico de peis.
     */
    public function show(Aluno $aluno): Response
    {
        $aluno->load(['deficiencias', 'turmas', 'peis']);

        // calcula a idade atual do discente a partir da data de nascimento
        $dataNasc = $aluno->data_nascimento ? Carbon::parse($aluno->data_nascimento) : null;
        $idadeCalculada = $dataNasc ? "{$dataNasc->age} anos (" . $dataNasc->format('d/m/Y') . ")" : 'Não informada';

        // mapeia e ordena as turmas vinculadas ao aluno
        $turmasOrdenadas = $aluno->turmas->map(function (Turma $turma) {
            return [
                'id' => $turma->id,
                'nome' => $turma->nome_curso,
                'ano' => (int) $turma->pivot->ano_letivo,
                'atual' => (bool) $turma->pivot->is_atual,
            ];
        })->sortByDesc('ano')->values();

        $turmaMaisRecente = $turmasOrdenadas->firstWhere('atual', true) ?? $turmasOrdenadas->first();
        $turmasAnteriores = $turmasOrdenadas->filter(fn ($t) => !$t['atual'] || ($turmaMaisRecente && $t['id'] !== $turmaMaisRecente['id']))->values();

        // obtém o pei mais recente para exibir os dados da ficha
        $peiMaisRecente = $aluno->peis()->latest('ano_letivo')->first();

        $fichaPei = [
            'existe' => (bool) $peiMaisRecente,
            'pei_id' => $peiMaisRecente?->id,
            'pei_codigo' => $peiMaisRecente?->codigo,
            'pei_ano' => $peiMaisRecente?->ano_letivo,
            'diagnostico' => $peiMaisRecente?->diagnostico ?? ($aluno->deficiencias->map(fn($d) => "[{$d->codigo}] {$d->nome}")->join(', ') ?: null),
            'tempo_livre' => $peiMaisRecente?->tempo_livre,
            'pratica_esporte' => $peiMaisRecente?->pratica_esporte,
            'caracteristicas_discente' => $peiMaisRecente?->caracteristicas_discente,
            'organizacao_e_prazos' => $peiMaisRecente?->organizacao_e_prazos,
            'dificuldade_interacao_social' => $peiMaisRecente?->dificuldade_interacao_social,
            'sugestoes_recursos_estrategias' => $peiMaisRecente?->sugestoes_recursos_estrategias,
        ];

        $historicoPeis = $aluno->peis->map(function ($pei) {
            return [
                'id' => $pei->id,
                'codigo' => $pei->codigo,
                'ano' => $pei->ano_letivo,
                'status' => $pei->status ?? 'Ativo',
                'data_criacao' => $pei->data_elaboracao ? Carbon::parse($pei->data_elaboracao)->format('d/m/Y') : '-',
            ];
        });

        $turmasDisponiveis = Turma::where('is_active', true)
            ->select('id', 'nome_curso', 'ano_letivo')
            ->orderBy('ano_letivo', 'desc')
            ->orderBy('nome_curso', 'asc')
            ->get();

        return Inertia::render('Alunos/Show', [
            'aluno' => [
                'id' => $aluno->id,
                'nome' => $aluno->nome,
                'matricula' => $aluno->matricula,
                'data_nascimento' => $aluno->data_nascimento?->format('Y-m-d'),
                'idade_formatada' => $idadeCalculada,
                'foto_url' => $aluno->foto_url,
                'is_active' => (bool) $aluno->is_active,
                'deficiencias' => $aluno->deficiencias->map(fn ($d) => [
                    'id' => $d->id,
                    'codigo' => $d->codigo,
                    'nome' => $d->nome,
                ]),
                'turma_atual' => $turmaMaisRecente,
                'turmas_anteriores' => $turmasAnteriores,
                'ficha_pei' => $fichaPei,
                'historico_peis' => $historicoPeis,
            ],
            'turmasDisponiveis' => $turmasDisponiveis,
        ]);
    }

    /**
     * exibe o formulário de edição do aluno.
     */
    public function edit(Aluno $aluno): Response
    {
        $this->ensureAdminOrNai();
        $aluno->load(['deficiencias', 'turmas']);

        $deficiencias = Deficiencia::where('is_active', true)
            ->select('id', 'codigo', 'nome')
            ->orderBy('nome', 'asc')
            ->get();

        $turmas = Turma::where('is_active', true)
            ->select('id', 'nome_curso', 'ano_letivo')
            ->orderBy('ano_letivo', 'desc')
            ->orderBy('nome_curso', 'asc')
            ->get();

        $turmaAtual = $aluno->turmaAtual();

        return Inertia::render('Alunos/Edit', [
            'aluno' => [
                'id' => $aluno->id,
                'nome' => $aluno->nome,
                'matricula' => $aluno->matricula,
                'data_nascimento' => $aluno->data_nascimento?->format('Y-m-d'),
                'foto_url' => $aluno->foto_url,
                'deficiencias_ids' => $aluno->deficiencias->pluck('id')->toArray(),
                'turma_id' => $turmaAtual?->id,
            ],
            'deficienciasDisponiveis' => $deficiencias,
            'turmasDisponiveis' => $turmas,
        ]);
    }

    /**
     * atualiza os dados cadastrais, deficiências e turma do aluno.
     */
    public function update(Request $request, Aluno $aluno): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'matricula' => ['required', 'string', 'max:50', Rule::unique('alunos', 'matricula')->ignore($aluno->id)],
            'data_nascimento' => ['required', 'date', 'before_or_equal:today'],
            'deficiencias' => ['nullable', 'array'],
            'deficiencias.*' => ['exists:deficiencias,id'],
            'turma_id' => ['nullable', 'exists:turmas,id'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remover_foto' => ['nullable', 'boolean'],
        ], [
            'foto.image' => 'O arquivo enviado deve ser uma imagem.',
            'foto.mimes' => 'A foto deve estar no formato JPG, PNG ou WEBP.',
            'foto.max' => 'A foto deve ter no máximo 2 MB.',
            'foto.uploaded' => 'Não foi possível enviar a foto. Tente uma imagem menor.',
        ]);

        $turmaAtualAntiga = $aluno->turmaAtual();
        $dadosAnteriores = [
            'nome' => $aluno->nome,
            'matricula' => $aluno->matricula,
            'data_nascimento' => $aluno->data_nascimento?->format('d/m/Y'),
            'deficiencias' => $aluno->deficiencias->pluck('nome')->values()->toArray(),
            'turma' => $turmaAtualAntiga ? "{$turmaAtualAntiga->nome_curso} ({$turmaAtualAntiga->pivot->ano_letivo})" : 'Não vinculado',
            'foto' => $aluno->foto_path ? 'Possui foto' : 'Sem foto',
        ];

        // processa a substituição ou remoção da foto de perfil do discente de forma segura
        $fotoAnterior = $aluno->foto_path;
        $fotoPath = $fotoAnterior;
        $novoPath = null;
        $deveRemoverAnterior = false;

        if ($request->hasFile('foto')) {
            $novoPath = $request->file('foto')->store(self::FOTO_DIR, self::FOTO_DISK);
            $fotoPath = $novoPath;
            $deveRemoverAnterior = (bool) $fotoAnterior;
        } elseif ($request->boolean('remover_foto') && $fotoAnterior) {
            $fotoPath = null;
            $deveRemoverAnterior = true;
        }

        try {
            DB::transaction(function () use ($request, $aluno, $validated, $fotoPath) {
                $aluno->update([
                    'nome' => $validated['nome'],
                    'matricula' => $validated['matricula'],
                    'data_nascimento' => $validated['data_nascimento'],
                    'foto_path' => $fotoPath,
                ]);

                $aluno->deficiencias()->sync($validated['deficiencias'] ?? []);

                if (!empty($validated['turma_id'])) {
                    $turma = Turma::find($validated['turma_id']);
                    // desmarca as turmas anteriores como turma atual
                    $aluno->turmas()->updateExistingPivot($aluno->turmas->pluck('id'), ['is_atual' => false]);

                    if ($aluno->turmas()->where('turma_id', $turma->id)->exists()) {
                        $aluno->turmas()->updateExistingPivot($turma->id, ['is_atual' => true, 'ano_letivo' => $turma->ano_letivo]);
                    } else {
                        $aluno->turmas()->attach($turma->id, [
                            'ano_letivo' => $turma->ano_letivo,
                            'is_atual' => true,
                        ]);
                    }
                } elseif ($request->has('turma_id') && empty($validated['turma_id'])) {
                    $aluno->turmas()->updateExistingPivot($aluno->turmas->pluck('id'), ['is_atual' => false]);
                }
            });
        } catch (\Throwable $e) {
            if ($novoPath) {
                Storage::disk(self::FOTO_DISK)->delete($novoPath);
            }
            throw $e;
        }

        if ($deveRemoverAnterior && $fotoAnterior) {
            Storage::disk(self::FOTO_DISK)->delete($fotoAnterior);
        }

        $turmaAtualNova = $aluno->fresh()->turmaAtual();

        // registra a atualização dos dados do aluno na trilha de auditoria
        AuditService::log(
            'Alunos',
            'Atualização',
            "Atualizou os dados cadastrais do aluno(a) {$aluno->nome}",
            $aluno,
            $dadosAnteriores,
            [
                'nome' => $aluno->nome,
                'matricula' => $aluno->matricula,
                'data_nascimento' => $aluno->data_nascimento?->format('d/m/Y'),
                'deficiencias' => Deficiencia::whereIn('id', $validated['deficiencias'] ?? [])->pluck('nome')->values()->toArray(),
                'turma' => $turmaAtualNova ? "{$turmaAtualNova->nome_curso} ({$turmaAtualNova->pivot->ano_letivo})" : 'Não vinculado',
                'foto' => $aluno->foto_path ? 'Possui foto' : 'Sem foto',
            ]
        );

        return redirect()->route('alunos.show', $aluno->id)->with('success', 'Dados do aluno atualizados com sucesso.');
    }

    /**
     * alterna o status de ativo/inativo do aluno.
     */
    public function destroy(Aluno $aluno): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $aluno->is_active = !$aluno->is_active;
        $aluno->save();

        $statusMsg = $aluno->is_active ? 'reativado' : 'inativado';
        $acao = $aluno->is_active ? 'Reativação' : 'Inativação';

        // registra a alteração de status do aluno na trilha de auditoria
        AuditService::log(
            'Alunos',
            $acao,
            ($aluno->is_active ? 'Reativou' : 'Inativou') . " o aluno(a) {$aluno->nome}",
            $aluno,
            ['is_active' => !$aluno->is_active],
            ['is_active' => $aluno->is_active]
        );

        return redirect()->route('alunos.index')->with('success', "Aluno {$statusMsg} com sucesso.");
    }

    /**
     * associa o aluno a uma nova turma no ano letivo indicado.
     */
    public function associarTurma(Request $request, Aluno $aluno): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $validated = $request->validate([
            'turma_id' => ['required', 'exists:turmas,id'],
            'ano_letivo' => ['required', 'integer'],
        ]);

        $turmaAntiga = $aluno->turmaAtual();
        $dadosAnteriores = [
            'turma' => $turmaAntiga ? "{$turmaAntiga->nome_curso} ({$turmaAntiga->pivot->ano_letivo})" : 'Não vinculado',
        ];

        DB::transaction(function () use ($aluno, $validated) {
            $aluno->turmas()->updateExistingPivot($aluno->turmas->pluck('id'), ['is_atual' => false]);

            if ($aluno->turmas()->where('turma_id', $validated['turma_id'])->exists()) {
                $aluno->turmas()->updateExistingPivot($validated['turma_id'], [
                    'ano_letivo' => $validated['ano_letivo'],
                    'is_atual' => true,
                ]);
            } else {
                $aluno->turmas()->attach($validated['turma_id'], [
                    'ano_letivo' => $validated['ano_letivo'],
                    'is_atual' => true,
                ]);
            }
        });

        $turma = Turma::find($validated['turma_id']);
        AuditService::log(
            'Alunos',
            'Atualização',
            "Associou o aluno(a) {$aluno->nome} à turma {$turma->nome_curso} ({$validated['ano_letivo']})",
            $aluno,
            $dadosAnteriores,
            [
                'turma' => "{$turma->nome_curso} ({$validated['ano_letivo']})",
            ]
        );

        return redirect()->route('alunos.show', $aluno->id)->with('success', 'Turma associada com sucesso.');
    }
}
