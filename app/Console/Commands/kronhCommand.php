<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Http\Controllers\sdk\Kronh;
use App\Services\KronhTrackerService;

class kronhCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:kronh-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commando para mandar las unidades de kronh';

    /**
     * Execute the console command.
     */
    public function handle(KronhTrackerService $kronhService)
    {
        $kronhController = new Kronh();

        $clientNames = [
            'Grupo Preuss - KRONH - HLK',
            'PICHARDO - KRONH'
        ];
        foreach ($clientNames as $name) {
            # code...
            $client = Client::where('name', $name)->first();
            
            if ($client) {
                $this->info("Procesando: $name");
                
                try {
                    $kronhController->tracking($kronhService, $client->id);
                } catch (\Exception $e) {
                    $this->error("Error procesando $name: " . $e->getMessage());
                    \Log::error("Error en comando kronh para $name: " . $e->getMessage());
                }

            } else {
                $this->error("Cliente no encontrado: $name");
            }
        }

         $this->info('Comando ejecutado correctamente para Kronh.');
    }
}
