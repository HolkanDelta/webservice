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
        $landstarController = new Landstar();
        $clientNames = [
            'Holkan Mapon-Landstar 1',
        ];

      
        foreach ($clientNames as $name) {
            $client = Client::where('name', $name)->where('token','>=',0)->first();
            if ($client) {
                $this->info("Procesando: $name");
                
                try {
                    $landstarController->landstarUnits($gpsService, $client);
                } catch (\Exception $e) {
                    $this->error("Error procesando $name: " . $e->getMessage());
                    \Log::error("Error en comando RecursoConfiable para $name: " . $e->getMessage());
                }

            } else {
                $this->error("Cliente no encontrado: $name");
            }
        }
        
        $this->info('Comando ejecutado correctamente para recurso confiable.');
    }
}
