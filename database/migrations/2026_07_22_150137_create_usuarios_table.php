<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->enum('tipo', ['professor', 'zelador', 'tecnico', 'outro']);
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();

            // Zeladores/técnicos usam senha fixa própria (não diária).
            // Fica null para professores, que usam a tabela senhas_diarias.
            $table->string('senha_fixa')->nullable();
            $table->timestamp('senha_fixa_atualizada_em')->nullable();

            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};