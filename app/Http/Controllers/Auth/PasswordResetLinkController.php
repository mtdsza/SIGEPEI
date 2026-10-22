<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * exibe a tela de solicitação de redefinição de senha.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * processa a solicitação de envio de link para redefinição de senha.
     */
    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('login')->with('status', 'O envio de e-mails está desativado. Por favor, solicite a redefinição de senha ao administrador.');
    }
}
