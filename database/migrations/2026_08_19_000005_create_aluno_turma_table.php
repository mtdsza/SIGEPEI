<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * executa a criação da tabela pivô de vínculo entre alunos e turmas.
     */
    public function up(): void
    {
        Schema::create('aluno_turma', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->integer('ano_letivo');
            $table->boolean('is_atual')->default(true);
            $table->timestamps();
        });
    }

    /**
     * reverte a criação da tabela pivô aluno_turma.
     */
    public function down(): void
    {
        Schema::dropIfExists('aluno_turma');
    }
};
