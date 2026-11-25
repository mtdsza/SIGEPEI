<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\AuditLog;
use App\Models\Deficiencia;
use App\Models\Disciplina;
use App\Models\Pei;
use App\Models\PeiApoio;
use App\Models\PeiDisciplina;
use App\Models\Turma;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\TestDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_database_seeder_creates_only_admin_user(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertDatabaseHas('users', [
            'email' => 'admin@sigepei.edu.br',
            'cargo' => 'NAI',
            'is_admin' => true,
            'is_active' => true,
        ]);

        $this->assertSame(0, Aluno::count());
        $this->assertSame(0, Turma::count());
        $this->assertSame(0, Disciplina::count());
        $this->assertSame(0, Deficiencia::count());
        $this->assertSame(0, Pei::count());
        $this->assertSame(0, AnoLetivo::count());
    }

    public function test_test_data_seeder_populates_complete_coherent_dataset_and_is_idempotent(): void
    {
        $this->seed(TestDataSeeder::class);

        $this->assertGreaterThanOrEqual(10, User::count());
        $this->assertDatabaseHas('users', ['email' => 'admin@sigepei.edu.br', 'is_admin' => true]);
        $this->assertDatabaseHas('users', ['email' => 'inativo@sigepei.edu.br', 'is_active' => false]);

        $this->assertSame(2, AnoLetivo::count());
        $this->assertGreaterThanOrEqual(8, Deficiencia::count());
        $this->assertGreaterThanOrEqual(8, Disciplina::count());
        $this->assertGreaterThanOrEqual(5, Turma::count());
        $this->assertSame(7, Aluno::count());
        $this->assertSame(6, Pei::count());
        $this->assertSame(3, PeiApoio::count());
        $this->assertGreaterThan(0, PeiDisciplina::count());
        $this->assertGreaterThan(0, AuditLog::count());

        // Verifica idempotência ao executar novamente
        $peisBefore = Pei::count();
        $usersBefore = User::count();
        $logsBefore = AuditLog::count();

        $this->seed(TestDataSeeder::class);

        $this->assertSame($peisBefore, Pei::count());
        $this->assertSame($usersBefore, User::count());
        $this->assertSame($logsBefore, AuditLog::count());
    }
}

