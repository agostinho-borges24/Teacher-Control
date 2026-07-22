<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sala_id')->constrained('salas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();

            $table->dateTime('entrada_em');
            $table->dateTime('saida_em')->nullable();

            // 'online' = validado direto no servidor
            // 'cache_offline' = veio do cache local do controlador, sincronizado depois
            $table->enum('origem', ['online', 'cache_offline'])->default('online');

            // ok | em_andamento | encerrado_por_proxima_entrada
            // | divergencia_usuario | encerrado_por_grade | sem_confirmacao_fim_do_dia
            $table->string('status')->default('em_andamento');

            $table->timestamps();

            $table->index(['sala_id', 'entrada_em']);
            $table->index(['usuario_id', 'entrada_em']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessoes');
    }
};