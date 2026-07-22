<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('senhas_diarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();

            // Senha válida em QUALQUER sala em que o professor tenha aula nesse dia.
            $table->string('senha');
            $table->date('data_referencia');

            $table->dateTime('valida_de');
            $table->dateTime('valida_ate');

            $table->boolean('enviado_sms')->default(false);
            $table->boolean('enviado_email')->default(false);

            $table->timestamps();

            // A senha precisa ser única DENTRO do mesmo dia, já que não há
            // mais o contexto da sala para desambiguar quem a usou.
            $table->unique(['senha', 'data_referencia']);

            // Um professor tem só uma senha por dia.
            $table->unique(['usuario_id', 'data_referencia']);

            $table->index('data_referencia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('senhas_diarias');
    }
};