<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * executa a criação das tabelas de equipes de apoio do pei.
     */
    public function up(): void
    {
        // equipes de apoio multidisciplinar vinculadas ao pei
        Schema::create('pei_apoios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pei_id')->constrained('peis')->cascadeOnDelete();
            $table->string('nome'); // nome identificador da equipe de apoio
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // responsáveis por trimestre de cada equipe de apoio
        Schema::create('pei_apoio_responsaveis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pei_apoio_id')->constrained('pei_apoios')->cascadeOnDelete();
            $table->unsignedTinyInteger('trimestre'); // número do trimestre vigente (1, 2 ou 3)
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('nome_externo')->nullable();
            $table->timestamps();
        });

        // ações e atividades desenvolvidas pela equipe de apoio em cada trimestre
        Schema::create('pei_apoio_atividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pei_apoio_id')->constrained('pei_apoios')->cascadeOnDelete();
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
     * reverte a criação das tabelas de equipes de apoio.
     */
    public function down(): void
    {
        Schema::dropIfExists('pei_apoio_atividades');
        Schema::dropIfExists('pei_apoio_responsaveis');
        Schema::dropIfExists('pei_apoios');
    }
};
