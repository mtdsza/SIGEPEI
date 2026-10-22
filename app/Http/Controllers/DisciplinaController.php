<?php

namespace App\Http\Controllers;

use App\Models\Disciplina;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DisciplinaController extends Controller
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
        $this->ensureAdminOrNai();
        $disciplinas = Disciplina::orderBy('nome', 'asc')
            ->get()
            ->map(function (Disciplina $d) {
                return [
                    'id' => $d->id,
                    'codigo' => $d->codigo,
                    'nome' => $d->nome,
                    'ativo' => (bool) $d->is_active,
                ];
            });

        return Inertia::render('Disciplinas/Index', [
            'disciplinas' => $disciplinas,
        ]);
    }

    public function create(): Response
    {
        $this->ensureAdminOrNai();
        return Inertia::render('Disciplinas/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:50'],
            'nome' => ['required', 'string', 'max:255'],
        ]);

        $disciplina = Disciplina::create([
            'codigo' => $validated['codigo'],
            'nome' => $validated['nome'],
            'is_active' => true,
        ]);

        // registra a criação da disciplina na trilha de auditoria
        AuditService::log(
            'Disciplinas',
            'Criação',
            "Cadastrou a disciplina [{$disciplina->codigo}] {$disciplina->nome}",
            $disciplina,
            null,
            ['codigo' => $disciplina->codigo, 'nome' => $disciplina->nome]
        );

        return redirect()->route('disciplinas.index')->with('success', 'Disciplina criada com sucesso.');
    }

    public function edit(Disciplina $disciplina): Response
    {
        $this->ensureAdminOrNai();
        return Inertia::render('Disciplinas/Edit', [
            'disciplina' => [
                'id' => $disciplina->id,
                'codigo' => $disciplina->codigo,
                'nome' => $disciplina->nome,
                'is_active' => (bool) $disciplina->is_active,
            ],
        ]);
    }

    public function update(Request $request, Disciplina $disciplina): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:50'],
            'nome' => ['required', 'string', 'max:255'],
        ]);

        $dadosAnt = ['codigo' => $disciplina->codigo, 'nome' => $disciplina->nome];
        $disciplina->update($validated);

        // registra a atualização da disciplina na trilha de auditoria
        AuditService::log(
            'Disciplinas',
            'Atualização',
            "Atualizou a disciplina [{$disciplina->codigo}] {$disciplina->nome}",
            $disciplina,
            $dadosAnt,
            ['codigo' => $disciplina->codigo, 'nome' => $disciplina->nome]
        );

        return redirect()->route('disciplinas.index')->with('success', 'Disciplina atualizada com sucesso.');
    }

    public function destroy(Disciplina $disciplina): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $disciplina->is_active = !$disciplina->is_active;
        $disciplina->save();

        $statusMsg = $disciplina->is_active ? 'reativada' : 'inativada';
        $acao = $disciplina->is_active ? 'Reativação' : 'Inativação';

        // registra a alteração de status da disciplina na trilha de auditoria
        AuditService::log(
            'Disciplinas',
            $acao,
            ($disciplina->is_active ? 'Reativou' : 'Inativou') . " a disciplina {$disciplina->nome}",
            $disciplina,
            ['is_active' => !$disciplina->is_active],
            ['is_active' => $disciplina->is_active]
        );

        return redirect()->route('disciplinas.index')->with('success', "Disciplina {$statusMsg} com sucesso.");
    }
}
