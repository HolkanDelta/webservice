<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\sdk\sdkfleet;
use App\Models\Client;

class fleetRocket_command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fleet-rocket_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta el comando para actualizar unidades del cliente de Fleet Rocket';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = Client::where('name', 'David Murillo Muñoz')->first();
        if ($client) {
            $sdkfleet = new sdkfleet();
            $sdkfleet->tracking($client->id);
        } else {
            $this->error('Cliente David Murillo Muñoz no encontrado.');
            return;
        }
        $this->info('Comando ejecutado correctamente para Fleet Rocket.');
    }
}
