<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * executa a criação da tabela de anos letivos e seus períodos trimestrais.
     */
    public function up(): void
    {
        Schema::create('anos_letivos', function (Blueprint $table) {
            $table->id();
            $table->integer('ano')->unique();
            $table->boolean('concluido')->default(false);
            $table->date('trimestre1_inicio')->nullable();
            $table->date('trimestre1_fim')->nullable();
            $table->date('trimestre2_inicio')->nullable();
            $table->date('trimestre2_fim')->nullable();
            $table->date('trimestre3_inicio')->nullable();
            $table->date('trimestre3_fim')->nullable();
            $table->timestamps();
        });
    }

    /**
     * reverte a criação da tabela de anos letivos.
     */
    public function down(): void
    {
        Schema::dropIfExists('anos_letivos');
    }
};
