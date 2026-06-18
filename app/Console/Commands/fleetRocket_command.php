<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\sdk\sdkfleet;
use App\Models\Client;

class fleetRocket_command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fleet-rocket_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta el comando para actualizar unidades del cliente de Fleet Rocket';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $service = \App\Models\Service::where('name', 'MAPON_FLEET_ROCKET')->first();
        if (!$service) {
            $this->error('Servicio MAPON_FLEET_ROCKET no encontrado.');
            return;
        }

        $clients = $service->clients;
        if ($clients->isEmpty()) {
            $this->warn('No hay clientes asignados al servicio MAPON_FLEET_ROCKET.');
            $service->logRun('success', 'No hay clientes asignados.', 0);
            return;
        }

        $sdkfleet = new sdkfleet();
        $failedClients = [];
        $successCount = 0;
        $payload = [];
        foreach ($clients as $client) {
            $this->info("Procesando cliente Fleet Rocket: {$client->name}");
            try {
                $res = $sdkfleet->tracking($client->id);
                $successCount++;
                $payload[] = [
                    'client' => $client->name,
                    'status' => 'success',
                    'message' => 'Sincronizado correctamente',
                    'transmission_payload' => count($res) === 1 ? ($res[0]['payload'] ?? null) : array_column($res, 'payload'),
                    'transmission_response' => count($res) === 1 ? ($res[0]['resultado'] ?? null) : array_column($res, 'resultado'),
                ];
            } catch (\Exception $e) {
                $failedClients[] = "{$client->name}: " . $e->getMessage();
                $payload[] = [
                    'client' => $client->name,
                    'status' => 'failure',
                    'message' => $e->getMessage(),
                    'transmission_payload' => null,
                    'transmission_response' => null,
                ];
                $this->error("Error procesando {$client->name}: " . $e->getMessage());
            }
        }

        $runtimeMs = round((microtime(true) - $startTime) * 1000);
        $totalCount = count($clients);

        if (count($failedClients) === 0) {
            $status = 'success';
            $message = "Sincronizados correctamente {$successCount} de {$totalCount} clientes.";
        } elseif ($successCount > 0) {
            $status = 'partial';
            $message = "Sincronizados {$successCount} de {$totalCount} clientes. Errores: " . implode(', ', $failedClients);
        } else {
            $status = 'failure';
            $message = "Todos los clientes fallaron ({$totalCount}). Errores: " . implode(', ', $failedClients);
        }

        $service->logRun($status, $message, $runtimeMs, $payload);
        $this->info('Comando ejecutado correctamente para Fleet Rocket.');
    }
}
