<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Http\Controllers\sdk\controlT;

class controlTcommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:control-tcommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for get control T log';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $controlController = new controlT();

        $service = \App\Models\Service::where('name', 'MAPON_CONTROL_T')->first();
        if (!$service) {
            $this->error('Servicio MAPON_CONTROL_T no encontrado.');
            return;
        }

        $clients = $service->clients()->where('token', '>=', 0)->get();
        if ($clients->isEmpty()) {
            $this->warn('No hay clientes con tokens válidos asignados al servicio MAPON_CONTROL_T.');
            $service->logRun('success', 'No hay clientes con tokens válidos asignados.', 0);
            return;
        }

        $failedClients = [];
        $successCount = 0;
        $payload = [];
        foreach ($clients as $client) {
            $this->info("Procesando: {$client->name}");
            
            try {
                $res = $controlController->tracking($client->id);
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
                \Log::error("Error en comando controlT para {$client->name}: " . $e->getMessage());
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
        $this->info('Comando ejecutado correctamente para control T.');
    }
}
