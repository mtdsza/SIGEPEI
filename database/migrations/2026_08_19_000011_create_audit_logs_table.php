<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * executa a criação da tabela de registros da trilha de auditoria.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_cargo')->nullable();
            $table->string('modulo'); // módulo auditado do sistema (peis, alunos, turmas, etc.)
            $table->string('acao'); // tipo de ação realizada (criação, atualização, etc.)
            $table->string('entidade_tipo')->nullable(); // tipo da entidade auditada (pei, aluno, turma, etc.)
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->text('descricao');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('dados_anteriores')->nullable();
            $table->json('dados_novos')->nullable();
            $table->timestamps();

            $table->index(['modulo', 'acao']);
            $table->index('created_at');
            $table->index('user_id');
        });
    }

    /**
     * reverte a criação da tabela de auditoria.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
