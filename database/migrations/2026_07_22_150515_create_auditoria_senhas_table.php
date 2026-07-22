<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_senhas', function (Blueprint $table) {
            $table->id();

            // Quem consultou (usuário do painel, não professor)
            $table->foreignId('operador_id')->constrained('users')->cascadeOnDelete();

            // Senha diária consultada
            $table->foreignId('senha_diaria_id')->constrained('senhas_diarias')->cascadeOnDelete();

            // 'revelar' | 'regenerar'
            $table->enum('acao', ['revelar', 'regenerar']);

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_senhas');
    }
};