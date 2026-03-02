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
        $rcController = new RcController();

        $clientNames = [
            'Transportes Pichardo',
            'MONICA CORONA LINARES',
            'LOGISTICA Y MANIOBRAS CAVA',
            'Hector Manuel Orozco',
            'Ernesto Soto Molina - Recurso Confiable WALMART',
            'Ernesto Soto Molina',
            'Ramiro Enrique Vargas Romero',
            'TMV',
            'Holkan - Doble cero - RC',
            'Conrado Martinez Tehuitzil',
            'PALLUS CARGO',
            'TRAVILSA MP',
            'Transportes Terrestres Vazquez',
            'Filiberto Villaseñor Villaseñor',
            'JOSE JORGE HUITZIL SANTIAGO',
        ];

      
        foreach ($clientNames as $name) {
            $client = Client::where('name', $name)->where('token','>=',0)->first();
            if ($client) {
                $this->info("Procesando: $name");
                
                try {
                    $rcController->RCServiceUnits($gpsService, $client);
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
