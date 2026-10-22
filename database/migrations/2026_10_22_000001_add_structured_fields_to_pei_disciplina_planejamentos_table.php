<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * executa a inclusão dos campos estruturados para estratégias, tempo e avaliação no planejamento da disciplina.
     */
    public function up(): void
    {
        Schema::table('pei_disciplina_planejamentos', function (Blueprint $table) {
            $table->json('estrategias_ensino')->nullable();
            $table->text('estrategias_ensino_outros')->nullable();
            $table->json('estrategias_avaliacao')->nullable();
            $table->text('estrategias_avaliacao_outros')->nullable();
            $table->string('tempo_opcao')->nullable();
            $table->string('tempo_outro')->nullable();
            $table->json('avaliacao_desempenho')->nullable();
            $table->text('avaliacao_desempenho_outros')->nullable();
        });
    }

    /**
     * reverte a inclusão dos campos estruturados.
     */
    public function down(): void
    {
        Schema::table('pei_disciplina_planejamentos', function (Blueprint $table) {
            $table->dropColumn([
                'estrategias_ensino',
                'estrategias_ensino_outros',
                'estrategias_avaliacao',
                'estrategias_avaliacao_outros',
                'tempo_opcao',
                'tempo_outro',
                'avaliacao_desempenho',
                'avaliacao_desempenho_outros',
            ]);
        });
    }
};
