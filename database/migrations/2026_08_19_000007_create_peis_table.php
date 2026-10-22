<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * executa a criação das tabelas principais e de apoio inicial do pei.
     */
    public function up(): void
    {
        // tabela principal de planos educacionais individualizados (peis)
        Schema::create('peis', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('turma_id')->nullable()->constrained('turmas')->nullOnDelete();
            $table->integer('ano_letivo');
            $table->date('data_elaboracao');
            $table->text('diagnostico')->nullable();

            // dados da ficha do discente (campos pedagógicos e comportamentais)
            $table->text('tempo_livre')->nullable();
            $table->text('pratica_esporte')->nullable();
            $table->text('caracteristicas_discente')->nullable();
            $table->text('organizacao_e_prazos')->nullable();
            $table->text('dificuldade_interacao_social')->nullable();
            $table->text('sugestoes_recursos_estrategias')->nullable();

            $table->string('status')->default('Ativo');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // responsáveis pelo pei em cada seção ou trimestre
        Schema::create('pei_responsaveis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pei_id')->constrained('peis')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('nome_externo')->nullable(); // nome de responsáveis sem cadastro de usuário no sistema
            $table->string('secao')->default('ficha_inicial'); // identifica a seção do pei à qual o responsável pertence
            $table->timestamps();
        });

        // identificações de habilidades, dificuldades e fatores contextuais do discente
        Schema::create('pei_identificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pei_id')->constrained('peis')->cascadeOnDelete();
            $table->enum('tipo', [
                'habilidade',
                'dificuldade',
                'dificultador_interno',
                'facilitador_interno',
                'dificultador_externo',
                'facilitador_externo',
            ]);
            $table->text('descricao');
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // propostas e possibilidades de intervenção pedagógica
        Schema::create('pei_intervencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pei_id')->constrained('peis')->cascadeOnDelete();
            $table->text('demanda');
            $table->text('encaminhamento_estrategia');
            $table->text('responsavel');
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // ações e atividades de acompanhamento trimestral desenvolvidas pelo nai
        Schema::create('pei_nai_atividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pei_id')->constrained('peis')->cascadeOnDelete();
            $table->unsignedTinyInteger('trimestre'); // número do trimestre vigente (1, 2 ou 3)
            $table->text('objetivos');
            $table->text('recursos_acoes');
            $table->string('tempo');
            $table->text('avaliacao_resultados')->nullable();
            $table->text('sugestoes_aproveitamento')->nullable();
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    /**
     * reverte a criação das tabelas do pei.
     */
    public function down(): void
    {
        Schema::dropIfExists('pei_nai_atividades');
        Schema::dropIfExists('pei_intervencoes');
        Schema::dropIfExists('pei_identificacoes');
        Schema::dropIfExists('pei_responsaveis');
        Schema::dropIfExists('peis');
    }
};
