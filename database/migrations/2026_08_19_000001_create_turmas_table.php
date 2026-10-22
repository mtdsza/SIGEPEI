<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * executa a criação da tabela de turmas.
     */
    public function up(): void
    {
        Schema::create('turmas', function (Blueprint $table) {
            $table->id();
            $table->string('nome_curso');
            $table->integer('ano_letivo');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * reverte a criação da tabela de turmas.
     */
    public function down(): void
    {
        Schema::dropIfExists('turmas');
    }
};
