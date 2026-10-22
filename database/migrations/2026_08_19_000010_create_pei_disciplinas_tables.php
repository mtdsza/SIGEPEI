<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * executa a criação das tabelas de disciplinas, planejamento e avaliações do pei.
     */
    public function up(): void
    {
        // registro trimestral das disciplinas vinculadas ao pei
        Schema::create('pei_disciplinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pei_id')->constrained('peis')->cascadeOnDelete();
            $table->foreignId('disciplina_id')->constrained('disciplinas')->cascadeOnDelete();
            $table->unsignedTinyInteger('trimestre'); // número do trimestre vigente (1, 2 ou 3)
            $table->json('grau_autonomia')->nullable(); // opções de grau de autonomia e compreensão do estudante
            $table->string('especifique_outros')->nullable(); // especificação textual para outros instrumentos de avaliação
            $table->timestamps();

            $table->unique(['pei_id', 'disciplina_id', 'trimestre']);
        });

        // parte 1: planejamentos pedagógicos e adaptações curriculares da disciplina
        Schema::create('pei_disciplina_planejamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pei_disciplina_id')->constrained('pei_disciplinas')->cascadeOnDelete();
            $table->text('objetivos'); // objetivos educacionais e pedagógicos
            $table->text('conteudos'); // conteúdos curriculares adaptados
            $table->text('metodologias_recursos'); // metodologias e recursos didáticos utilizados
            $table->string('tempo'); // tempo estimado de execução
            $table->text('avaliacao_resultados')->nullable(); // avaliação dos resultados e habilidades demonstradas
            $table->text('sugestoes_aproveitamento')->nullable(); // sugestões para aprimoramento do aprendizado
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // parte 2: instrumentos e notas de avaliação adotados na disciplina
        Schema::create('pei_disciplina_avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pei_disciplina_id')->constrained('pei_disciplinas')->cascadeOnDelete();
            $table->string('tipo'); // tipo de instrumento avaliativo (prova_escrita, prova_pratica, etc.)
            $table->boolean('adotado')->default(false);
            $table->string('valor_1')->nullable();
            $table->string('nota_1')->nullable();
            $table->string('valor_2')->nullable();
            $table->string('nota_2')->nullable();
            $table->string('valor_3')->nullable();
            $table->string('nota_3')->nullable();
            $table->timestamps();
        });
    }

    /**
     * reverte a criação das tabelas de disciplinas do pei.
     */
    public function down(): void
    {
        Schema::dropIfExists('pei_disciplina_avaliacoes');
        Schema::dropIfExists('pei_disciplina_planejamentos');
        Schema::dropIfExists('pei_disciplinas');
    }
};
