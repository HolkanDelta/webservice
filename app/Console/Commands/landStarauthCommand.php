<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\sdk\Landstar;
use App\Models\Client;
use App\Services\LandstarService;

class landStarauthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:land-starauth-command';

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
        $service = \App\Models\Service::where('name', 'MAPON_LANDSTAR')->first();
        if (!$service) {
            $this->error('Servicio MAPON_LANDSTAR no encontrado.');
            return;
        }

        $clients = $service->clients()->whereNotNull('company_id')->get();
        if ($clients->isEmpty()) {
            $this->warn('No hay clientes con company_id asignados al servicio MAPON_LANDSTAR.');
            $service->logRun('success', 'No hay clientes con company_id para autenticación.', 0);
            return;
        }

        $failedClients = [];
        $successCount = 0;
        $payload = [];
        foreach ($clients as $client) {
            $this->info("Autenticando Landstar para: {$client->name}");
            try {
                $rcController = new Landstar();
                $rcController->landstarauth($gpsService, $client);
                $successCount++;
                $payload[] = [
                    'client' => $client->name,
                    'status' => 'success',
                    'message' => 'Autenticado correctamente'
                ];
            } catch (\Exception $e) {
                $failedClients[] = "{$client->name}: " . $e->getMessage();
                $payload[] = [
                    'client' => $client->name,
                    'status' => 'failure',
                    'message' => $e->getMessage()
                ];
                $this->error("Error autenticando {$client->name}: " . $e->getMessage());
            }
        }
        
        $runtimeMs = round((microtime(true) - $startTime) * 1000);
        $totalCount = count($clients);

        if (count($failedClients) === 0) {
            $status = 'success';
            $message = "Autenticación completada correctamente para {$successCount} de {$totalCount} clientes.";
        } elseif ($successCount > 0) {
            $status = 'partial';
            $message = "Autenticación parcial. Éxito: {$successCount}/{$totalCount}. Errores: " . implode(', ', $failedClients);
        } else {
            $status = 'failure';
            $message = "Autenticación fallida para todos los clientes ({$totalCount}). Errores: " . implode(', ', $failedClients);
        }

        $service->logRun($status, $message, $runtimeMs, $payload);
        $this->info('Comando Login ejecutado correctamente para landstar.');
    }
}
