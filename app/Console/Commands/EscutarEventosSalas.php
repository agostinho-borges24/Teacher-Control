<?php

// app/Console/Commands/EscutarEventosSalas.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use App\Jobs\ProcessarEventoSala;

class EscutarEventosSalas extends Command
{
    protected $signature = 'mqtt:escutar-salas';
    protected $description = 'Escuta eventos MQTT publicados pelas fechaduras das salas';

    public function handle()
    {
        $mqtt = MQTT::connection('default');

        // universidade/salas/{sala_id}/eventos
        $mqtt->subscribe('universidade/salas/+/eventos', function (string $topic, string $message) {

            $salaId = explode('/', $topic)[2]; // extrai o ID da sala do tópico
            $payload = json_decode($message, true);

            if (!$payload) {
                logger()->warning("Payload MQTT inválido na sala {$salaId}: {$message}");
                return;
            }

            // Enfileira o processamento — não processa aqui dentro,
            // porque não queremos travar o listener esperando o banco
            ProcessarEventoSala::dispatch(
                salaId: $salaId,
                senha: $payload['senha'],
                tipo: $payload['tipo'],       // 'entrada' ou 'saida'
                timestamp: $payload['timestamp'],
                origem: $payload['origem'] ?? 'online', // 'online' ou 'cache_offline'
            );

        }, qos: 1); // QoS 1 = garante entrega pelo menos uma vez

        $this->info('Escutando eventos das salas...');
        $mqtt->loop(true); // mantém o processo vivo, bloqueante
    }
}