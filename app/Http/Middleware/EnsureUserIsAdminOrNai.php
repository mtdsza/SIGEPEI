<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdminOrNai
{
    /**
     * valida se o usuário autenticado possui perfil de administrador ou integrante do nai.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || (!$user->is_admin && $user->cargo !== 'NAI')) {
            abort(403, 'Acesso restrito à equipe do NAI e administradores.');
        }

        return $next($request);
    }
}
