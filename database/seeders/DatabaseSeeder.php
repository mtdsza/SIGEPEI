<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * seeder padrão do sistema: cria apenas o usuário administrador inicial.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sigepei.edu.br'],
            [
                'name' => 'Administrador SIGEPEI',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'cargo' => 'NAI',
                'is_admin' => true,
                'is_active' => true,
            ]
        );
    }
}
