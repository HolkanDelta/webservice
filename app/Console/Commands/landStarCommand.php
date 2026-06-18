<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Http\Controllers\sdk\Landstar;
use App\Services\LandstarService;

class landStarCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:land-star-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(LandstarService $gpsService)
    {
        $startTime = microtime(true);
        $landstarController = new Landstar();
        
        $service = \App\Models\Service::where('name', 'MAPON_LANDSTAR')->first();
        if (!$service) {
            $this->error('Servicio MAPON_LANDSTAR no encontrado.');
            return;
        }

        $clients = $service->clients()->where('token', '>=', 0)->get();
        if ($clients->isEmpty()) {
            $this->warn('No hay clientes con tokens válidos asignados al servicio MAPON_LANDSTAR.');
            $service->logRun('success', 'No hay clientes con tokens válidos asignados.', 0);
            return;
        }

        $failedClients = [];
        $successCount = 0;
        $payload = [];
        foreach ($clients as $client) {
            $this->info("Procesando: {$client->name}");
            
            try {
                $res = $landstarController->landstarUnits($gpsService, $client);
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
                \Log::error("Error en comando landStar para {$client->name}: " . $e->getMessage());
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
        $this->info('Comando ejecutado correctamente para Landstar.');
    }
}
