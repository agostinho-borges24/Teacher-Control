<?php

// app/Jobs/ProcessarEventoSala.php

namespace App\Jobs;

use App\Models\SenhaDiaria;
use App\Models\Sessao;
use App\Events\OcupacaoSalaAtualizada;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessarEventoSala implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $salaId,
        public string $senha,
        public string $tipo,
        public string $timestamp,
        public string $origem,
    ) {}

    public function handle(): void
    {
        // 1. Identifica o professor pela senha do dia
        $senhaDiaria = SenhaDiaria::where('senha', $this->senha)
            ->where('data_referencia', now()->toDateString())
            ->first();

        if (!$senhaDiaria) {
            logger()->warning("Senha inválida/expirada usada na sala {$this->salaId}");
            return; // não gera sessão, mas poderia gerar um log de tentativa inválida
        }

        $usuarioId = $senhaDiaria->usuario_id;

        if ($this->tipo === 'entrada') {
            $this->tratarEntrada($usuarioId);
        } else {
            $this->tratarSaida($usuarioId);
        }

        // Notifica o painel em tempo real
        broadcast(new OcupacaoSalaAtualizada($this->salaId));
    }

    private function tratarEntrada(int $usuarioId): void
    {
        // Se já existe sessão aberta nessa sala, fecha ela à força
        // (caso do professor que esqueceu de trancar)
        $sessaoAberta = Sessao::where('sala_id', $this->salaId)
            ->whereNull('saida_em')
            ->first();

        if ($sessaoAberta) {
            $sessaoAberta->update([
                'saida_em' => $this->timestamp,
                'status' => 'encerrado_por_proxima_entrada',
            ]);
        }

        Sessao::create([
            'sala_id' => $this->salaId,
            'usuario_id' => $usuarioId,
            'entrada_em' => $this->timestamp,
            'origem' => $this->origem,
            'status' => 'em_andamento',
        ]);
    }

    private function tratarSaida(int $usuarioId): void
    {
        $sessao = Sessao::where('sala_id', $this->salaId)
            ->whereNull('saida_em')
            ->latest('entrada_em')
            ->first();

        if (!$sessao) {
            logger()->warning("Saída sem entrada correspondente na sala {$this->salaId}");
            return;
        }

        $status = $sessao->usuario_id === $usuarioId ? 'ok' : 'divergencia_usuario';

        $sessao->update([
            'saida_em' => $this->timestamp,
            'status' => $status,
        ]);
    }
}