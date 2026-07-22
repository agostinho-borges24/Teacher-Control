<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salas', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // ex: "101", "Laboratório 2"
            $table->string('mqtt_topic_id')->unique(); // ex: "101" -> universidade/salas/101/eventos
            $table->time('horario_encerramento_turno')->nullable(); // fallback de fechamento automático
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salas');
    }
};