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
        $controlController = new controlT();

        $clientNames = [
            'Transportes Ruiz'
        ];
        foreach ($clientNames as $name) {
            # code...
            $client = Client::where('name', $name)->where('token','>=',0)->first();
            if ($client) {
                $this->info("Procesando: $name");
                
                try {
                    $controlController->tracking($client->id);
                } catch (\Exception $e) {
                    $this->error("Error procesando $name: " . $e->getMessage());
                    \Log::error("Error en comando RecursoConfiable para $name: " . $e->getMessage());
                }

            } else {
                $this->error("Cliente no encontrado: $name");
            }
        }

         $this->info('Comando ejecutado correctamente para control T.');

    }
}
