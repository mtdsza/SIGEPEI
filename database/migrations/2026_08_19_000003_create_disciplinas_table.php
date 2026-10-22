<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * executa a criação da tabela de disciplinas curriculares.
     */
    public function up(): void
    {
        Schema::create('disciplinas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo');
            $table->string('nome');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * reverte a criação da tabela de disciplinas.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplinas');
    }
};
