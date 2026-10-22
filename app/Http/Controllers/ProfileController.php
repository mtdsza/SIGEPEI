<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\AuditService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * exibe o formulário de edição do perfil do usuário autenticado.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * atualiza as informações de perfil do usuário autenticado.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $dadosAnteriores = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // registra a atualização do perfil na trilha de auditoria
        AuditService::log(
            'Usuários',
            'Atualização',
            "Usuário {$user->name} atualizou seus próprios dados de perfil.",
            $user,
            $dadosAnteriores,
            [
                'name' => $user->name,
                'email' => $user->email,
            ]
        );

        return Redirect::route('profile.edit');
    }

    /**
     * encerra e remove a conta do usuário autenticado.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // registra a exclusão da conta na trilha de auditoria
        AuditService::log(
            'Usuários',
            'Inativação',
            "Usuário {$user->name} ({$user->email}) encerrou e excluiu sua própria conta.",
            $user,
            ['name' => $user->name, 'email' => $user->email],
            null
        );

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
