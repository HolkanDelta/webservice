<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Http\Controllers\sdk\SkyAngel;


class skyAngelcommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sky-angelcommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $skyAngelController = new SkyAngel();
        $clientNames = [
            'Magdalena Martínez Garduño',
            'TRANSMARS',
        ];
        foreach ($clientNames as $clientName) {
            $client = Client::where('name', $clientName)->first();
            if ($client) {
                try {
                    $this->info("Procesando: $clientName");
                    $skyAngelController->skyangelUnits($client);
                } catch (\Exception $e) {
                    $this->error("Error procesando $clientName: " . $e->getMessage());
                    \Log::error("Error en comando skyAngel para $clientName: " . $e->getMessage());
                }
            } else {
                $this->error("Cliente no encontrado: $clientName");
            }
        }
         $this->info('Comando ejecutado correctamente para recurso Skytrack.');
    }
}
