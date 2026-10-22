<?php

namespace App\Http\Controllers;

use App\Models\Deficiencia;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DeficienciaController extends Controller
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
        $deficiencias = Deficiencia::orderBy('nome', 'asc')
            ->get()
            ->map(function (Deficiencia $d) {
                return [
                    'id' => $d->id,
                    'codigo' => $d->codigo,
                    'nome' => $d->nome,
                    'ativo' => (bool) $d->is_active,
                ];
            });

        return Inertia::render('Deficiencias/Index', [
            'deficiencias' => $deficiencias,
        ]);
    }

    public function create(): Response
    {
        $this->ensureAdminOrNai();
        return Inertia::render('Deficiencias/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:50'],
            'nome' => ['required', 'string', 'max:255'],
        ]);

        $deficiencia = Deficiencia::create([
            'codigo' => $validated['codigo'],
            'nome' => $validated['nome'],
            'is_active' => true,
        ]);

        // registra a criação da deficiência na trilha de auditoria
        AuditService::log(
            'Deficiências',
            'Criação',
            "Cadastrou a deficiência/necessidade [{$deficiencia->codigo}] {$deficiencia->nome}",
            $deficiencia,
            null,
            ['codigo' => $deficiencia->codigo, 'nome' => $deficiencia->nome]
        );

        return redirect()->route('deficiencias.index')->with('success', 'Deficiência cadastrada com sucesso.');
    }

    public function edit(Deficiencia $deficiencia): Response
    {
        $this->ensureAdminOrNai();
        return Inertia::render('Deficiencias/Edit', [
            'deficiencia' => [
                'id' => $deficiencia->id,
                'codigo' => $deficiencia->codigo,
                'nome' => $deficiencia->nome,
                'is_active' => (bool) $deficiencia->is_active,
            ],
        ]);
    }

    public function update(Request $request, Deficiencia $deficiencia): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:50'],
            'nome' => ['required', 'string', 'max:255'],
        ]);

        $dadosAnt = ['codigo' => $deficiencia->codigo, 'nome' => $deficiencia->nome];
        $deficiencia->update($validated);

        // registra a atualização da deficiência na trilha de auditoria
        AuditService::log(
            'Deficiências',
            'Atualização',
            "Atualizou a deficiência [{$deficiencia->codigo}] {$deficiencia->nome}",
            $deficiencia,
            $dadosAnt,
            ['codigo' => $deficiencia->codigo, 'nome' => $deficiencia->nome]
        );

        return redirect()->route('deficiencias.index')->with('success', 'Deficiência atualizada com sucesso.');
    }

    public function destroy(Deficiencia $deficiencia): RedirectResponse
    {
        $this->ensureAdminOrNai();
        $deficiencia->is_active = !$deficiencia->is_active;
        $deficiencia->save();

        $statusMsg = $deficiencia->is_active ? 'reativada' : 'inativada';
        $acao = $deficiencia->is_active ? 'Reativação' : 'Inativação';

        // registra a alteração de status da deficiência na trilha de auditoria
        AuditService::log(
            'Deficiências',
            $acao,
            ($deficiencia->is_active ? 'Reativou' : 'Inativou') . " a deficiência {$deficiencia->nome}",
            $deficiencia,
            ['is_active' => !$deficiencia->is_active],
            ['is_active' => $deficiencia->is_active]
        );

        return redirect()->route('deficiencias.index')->with('success', "Deficiência {$statusMsg} com sucesso.");
    }
}
