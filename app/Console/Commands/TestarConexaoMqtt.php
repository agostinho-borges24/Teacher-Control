<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

class TestarConexaoMqtt extends Command
{
    protected $signature = 'mqtt:testar';
    protected $description = 'Testa a conexão com o broker MQTT publicando e escutando uma mensagem de teste';

    public function handle()
    {
        $this->info('Conectando ao broker...');

        $mqtt = MQTT::connection('default');

        $mqtt->subscribe('teste/conexao', function (string $topic, string $message) {
            $this->info("Mensagem recebida em [{$topic}]: {$message}");
        }, 0);

        $this->info('Inscrito no tópico teste/conexao. Publicando mensagem de teste em 2 segundos...');

        // Publica uma mensagem de teste depois de garantir que já está inscrito
        $mqtt->publish('teste/conexao', 'Conexão MQTT funcionando! ' . now(), 0);

        $this->info('Aguardando mensagens (Ctrl+C para sair)...');
        $mqtt->loop(true, true, 5); // roda por até 5 segundos e sai
    }
}