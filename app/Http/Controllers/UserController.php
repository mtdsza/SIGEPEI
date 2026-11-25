<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * garante que o usuário atual possui permissão de administrador.
     */
    private function ensureIsAdmin(Request $request): void
    {
        if (!$request->user() || !$request->user()->is_admin) {
            abort(403, 'Acesso restrito a administradores.');
        }
    }

    /**
     * exibe a listagem de usuários cadastrados.
     */
    public function index(Request $request): Response
    {
        $this->ensureIsAdmin($request);

        $users = User::select('id', 'name', 'email', 'cargo', 'is_admin', 'is_active')
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render('Usuarios/Index', [
            'users' => $users,
        ]);
    }

    /**
     * exibe o formulário de criação de novo usuário.
     */
    public function create(Request $request): Response
    {
        $this->ensureIsAdmin($request);

        return Inertia::render('Usuarios/Create');
    }

    /**
     * armazena um novo usuário no banco de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureIsAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'cargo' => ['required', Rule::in(['NAI', 'Professor', 'Outros'])],
            'is_admin' => ['required', 'boolean'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'cargo' => $validated['cargo'],
            'is_admin' => (bool) $validated['is_admin'],
            'is_active' => true,
            'password' => Hash::make($validated['password']),
        ]);

        // registra a criação na trilha de auditoria
        AuditService::log(
            'Usuários',
            'Criação',
            "Cadastrou o usuário {$user->name} ({$user->email}) com perfil {$user->cargo}",
            $user,
            null,
            [
                'name' => $user->name,
                'email' => $user->email,
                'cargo' => $user->cargo,
                'is_admin' => $user->is_admin ? 'Sim' : 'Não',
            ]
        );

        return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso.');
    }

    /**
     * exibe o formulário de edição do usuário.
     */
    public function edit(Request $request, User $usuario): Response
    {
        $this->ensureIsAdmin($request);

        return Inertia::render('Usuarios/Edit', [
            'user' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'cargo' => $usuario->cargo ?? 'Professor',
                'is_admin' => (bool) $usuario->is_admin,
                'is_active' => (bool) $usuario->is_active,
            ],
        ]);
    }

    /**
     * atualiza os dados do usuário especificado.
     */
    public function update(Request $request, User $usuario): RedirectResponse
    {
        $this->ensureIsAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($usuario->id)],
            'cargo' => ['required', Rule::in(['NAI', 'Professor', 'Outros'])],
            'is_admin' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $dadosAnt = [
            'name' => $usuario->name,
            'email' => $usuario->email,
            'cargo' => $usuario->cargo,
            'is_admin' => $usuario->is_admin ? 'Sim' : 'Não',
        ];

        // garante que o administrador logado não remova seus próprios privilégios de administrador
        $isAdmin = (bool) $validated['is_admin'];
        if ($usuario->id === $request->user()->id && !$isAdmin) {
            $isAdmin = true;
        }

        $usuario->name = $validated['name'];
        $usuario->email = $validated['email'];
        $usuario->cargo = $validated['cargo'];
        $usuario->is_admin = $isAdmin;

        if (!empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
        }

        $usuario->save();

        // registra a atualização na trilha de auditoria
        AuditService::log(
            'Usuários',
            'Atualização',
            "Atualizou os dados cadastrais do usuário {$usuario->name}",
            $usuario,
            $dadosAnt,
            [
                'name' => $usuario->name,
                'email' => $usuario->email,
                'cargo' => $usuario->cargo,
                'is_admin' => $usuario->is_admin ? 'Sim' : 'Não',
            ]
        );

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    /**
     * alterna o status de ativo/inativo do usuário.
     */
    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        $this->ensureIsAdmin($request);

        if ($usuario->id === $request->user()->id) {
            return redirect()->route('usuarios.index')->with('error', 'Você não pode inativar ou alterar o status da sua própria conta.');
        }

        $usuario->is_active = !$usuario->is_active;
        if (!$usuario->is_active) {
            $usuario->remember_token = Str::random(60);
        }
        $usuario->save();

        // encerra sessões ativas no banco quando o usuário for inativado
        if (!$usuario->is_active && config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $usuario->id)
                ->delete();
        }

        $statusMsg = $usuario->is_active ? 'reativado' : 'inativado';
        $acao = $usuario->is_active ? 'Reativação' : 'Inativação';

        // registra a alteração de status na trilha de auditoria
        AuditService::log(
            'Usuários',
            $acao,
            ($usuario->is_active ? 'Reativou' : 'Inativou') . " o acesso do usuário {$usuario->name}",
            $usuario,
            ['is_active' => !$usuario->is_active],
            ['is_active' => $usuario->is_active]
        );

        return redirect()->route('usuarios.index')->with('success', "Usuário {$statusMsg} com sucesso.");
    }
}
