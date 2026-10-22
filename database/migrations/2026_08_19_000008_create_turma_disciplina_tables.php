<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * executa a criação das tabelas de relacionamento entre turmas, disciplinas e docentes.
     */
    public function up(): void
    {
        // tabela pivô de vínculo entre turmas e disciplinas da matriz
        Schema::create('turma_disciplina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->foreignId('disciplina_id')->constrained('disciplinas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['turma_id', 'disciplina_id']);
        });

        // tabela de associação de docentes às disciplinas da turma
        Schema::create('turma_disciplina_professor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turma_disciplina_id')->constrained('turma_disciplina')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_substituto')->default(false);
            $table->timestamps();
        });
    }

    /**
     * reverte a criação das tabelas de disciplinas e docentes da turma.
     */
    public function down(): void
    {
        Schema::dropIfExists('turma_disciplina_professor');
        Schema::dropIfExists('turma_disciplina');
    }
};
