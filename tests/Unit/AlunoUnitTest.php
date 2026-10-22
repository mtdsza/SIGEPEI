<?php

namespace Tests\Unit;

use App\Models\Aluno;
use App\Models\Turma;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlunoUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_aluno_idade_accessor_calculation(): void
    {
        $dataNascimento = Carbon::now()->subYears(16)->format('Y-m-d');

        $aluno = Aluno::create([
            'nome' => 'Aluno Idade Teste',
            'matricula' => '20261111',
            'data_nascimento' => $dataNascimento,
            'is_active' => true,
        ]);

        $this->assertEquals(16, $aluno->idade);
    }

    public function test_aluno_turma_atual_returns_current_turma(): void
    {
        $aluno = Aluno::create([
            'nome' => 'Aluno Turma Teste',
            'matricula' => '20262222',
            'data_nascimento' => '2008-01-01',
            'is_active' => true,
        ]);

        $turma2025 = Turma::create(['nome_curso' => '1º Ano', 'ano_letivo' => 2025]);
        $turma2026 = Turma::create(['nome_curso' => '2º Ano', 'ano_letivo' => 2026]);

        $aluno->turmas()->attach($turma2025->id, ['ano_letivo' => 2025, 'is_atual' => false]);
        $aluno->turmas()->attach($turma2026->id, ['ano_letivo' => 2026, 'is_atual' => true]);

        $turmaAtual = $aluno->turmaAtual();

        $this->assertNotNull($turmaAtual);
        $this->assertEquals($turma2026->id, $turmaAtual->id);
        $this->assertEquals('2º Ano', $turmaAtual->nome_curso);
    }

    public function test_aluno_turma_atual_fallbacks_to_most_recent_year_when_is_atual_not_flagged(): void
    {
        $aluno = Aluno::create([
            'nome' => 'Aluno Fallback Teste',
            'matricula' => '20263333',
            'data_nascimento' => '2008-01-01',
            'is_active' => true,
        ]);

        $turmaAntiga = Turma::create(['nome_curso' => 'Ano 2024', 'ano_letivo' => 2024]);
        $turmaRecente = Turma::create(['nome_curso' => 'Ano 2025', 'ano_letivo' => 2025]);

        $aluno->turmas()->attach($turmaAntiga->id, ['ano_letivo' => 2024, 'is_atual' => false]);
        $aluno->turmas()->attach($turmaRecente->id, ['ano_letivo' => 2025, 'is_atual' => false]);

        $turmaAtual = $aluno->turmaAtual();

        $this->assertNotNull($turmaAtual);
        $this->assertEquals($turmaRecente->id, $turmaAtual->id);
    }
}
