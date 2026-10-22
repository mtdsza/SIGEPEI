<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * exibe a tela de login.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * processa a solicitação de autenticação do usuário.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // registra o login na trilha de auditoria
        AuditService::log('Autenticação', 'Login', 'Usuário efetuou login no sistema.');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * encerra a sessão autenticada do usuário.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // registra o logout na trilha de auditoria
        AuditService::log('Autenticação', 'Logout', 'Usuário encerrou a sessão no sistema.');

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
