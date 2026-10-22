<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * registra serviços da aplicação no container de dependências.
     */
    public function register(): void
    {
    }

    /**
     * inicializa serviços e configurações da aplicação.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
