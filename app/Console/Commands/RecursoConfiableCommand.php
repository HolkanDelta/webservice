<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Http\Controllers\sdk\RcController;
use App\Services\RecursoConfiable;

class RecursoConfiableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recurso-confiable-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(RecursoConfiable $gpsService)
    {
        $startTime = microtime(true);
        $rcController = new RcController();

        $service = \App\Models\Service::where('name', 'MAPON - RECURSO_CONFIABLE')->first();
        if (!$service) {
            $this->error('Servicio MAPON - RECURSO_CONFIABLE no encontrado.');
            return;
        }

        $clients = $service->clients()->where('token', '>=', 0)->get();
        if ($clients->isEmpty()) {
            $this->warn('No hay clientes con tokens válidos asignados al servicio MAPON - RECURSO_CONFIABLE.');
            $service->logRun('success', 'No hay clientes con tokens válidos asignados.', 0);
            return;
        }

        $failedClients = [];
        $successCount = 0;
        $payload = [];
        foreach ($clients as $client) {
            $this->info("Procesando: {$client->name}");
            
            try {
                $res = $rcController->RCServiceUnits($gpsService, $client);
                $successCount++;
                $payload[] = [
                    'client' => $client->name,
                    'status' => 'success',
                    'message' => 'Sincronizado correctamente',
                    'transmission_payload' => $res['payload'] ?? null,
                    'transmission_response' => $res['resultado'] ?? null,
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
                \Log::error("Error en comando RecursoConfiable para {$client->name}: " . $e->getMessage());
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
        $this->info('Comando ejecutado correctamente para recurso confiable.');
    }
}
