<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * template blade base carregado na primeira visita à página.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * determina a versão atual dos assets para invalidação de cache.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * define as propriedades compartilhadas por padrão em todas as páginas do inertia.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
