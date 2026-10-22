<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * executa a criação da tabela pivô de deficiências associadas aos alunos.
     */
    public function up(): void
    {
        Schema::create('aluno_deficiencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('deficiencia_id')->constrained('deficiencias')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * reverte a criação da tabela pivô aluno_deficiencia.
     */
    public function down(): void
    {
        Schema::dropIfExists('aluno_deficiencia');
    }
};
