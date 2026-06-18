<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Http\Controllers\sdk\RcController;
use App\Services\RecursoConfiable;

class recursoTokenChange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recurso-token-change';

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
        $service = \App\Models\Service::where('name', 'MAPON - RECURSO_CONFIABLE')->first();
        if (!$service) {
            $this->error('Servicio MAPON - RECURSO_CONFIABLE no encontrado.');
            return;
        }

        $clients = $service->clients()->whereNotNull('company_id')->get();
        if ($clients->isEmpty()) {
            $this->warn('No hay clientes con company_id asignados al servicio MAPON - RECURSO_CONFIABLE.');
            $service->logRun('success', 'No hay clientes con company_id para autenticación.', 0);
            return;
        }

        $failedClients = [];
        $successCount = 0;
        $payload = [];
        foreach ($clients as $client) {
            $this->info("Autenticando Recurso Confiable para: {$client->name}");
            try {
                $rcController = new RcController();
                $resultado = $rcController->RCServiceLogin($gpsService, $client);
                $this->info($client->name);
                $this->info($resultado->getContent());
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
            $message = "Autenticación de Recurso Confiable completada para {$successCount} de {$totalCount} clientes.";
        } elseif ($successCount > 0) {
            $status = 'partial';
            $message = "Autenticación parcial de Recurso Confiable. Éxito: {$successCount}/{$totalCount}. Errores: " . implode(', ', $failedClients);
        } else {
            $status = 'failure';
            $message = "Autenticación de Recurso Confiable fallida ({$totalCount}). Errores: " . implode(', ', $failedClients);
        }

        $service->logRun($status, $message, $runtimeMs, $payload);
        $this->info('Comando Login ejecutado correctamente para recurso confiable.');
    }
}
