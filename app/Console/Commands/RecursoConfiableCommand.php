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
        // 1. Instanciamos el controlador UNA sola vez (ahorramos memoria)
        $rcController = new RcController();

        // 2. Definimos la lista de clientes a procesar
        $clientNames = [
            'LOGISTICA Y MANIOBRAS CAVA',
            'Hector Manuel Orozco',
            'Transportes Terrestres Vazquez',
        ];

        // 3. Iteramos sobre cada cliente
        foreach ($clientNames as $name) {
            $client = Client::where('name', $name)->where('company_id','>',0)->first();
            //dd($client);
            if ($client) {
                $this->info("Procesando: $name");
                
                // Procesamos usando la instancia única del controlador
                try {
                    $rcController->RCServiceUnits($gpsService, $client);
                } catch (\Exception $e) {
                    // Si falla el proceso de este cliente, lo registramos pero NO detenemos el script
                    $this->error("Error procesando $name: " . $e->getMessage());
                    \Log::error("Error en comando RecursoConfiable para $name: " . $e->getMessage());
                }

            } else {
                // Si no encuentra al cliente, avisa pero CONTINÚA con el siguiente
                $this->error("Cliente no encontrado: $name");
            }
        }
        $this->info('Comando ejecutado correctamente para recurso confiable.');
    }
}
