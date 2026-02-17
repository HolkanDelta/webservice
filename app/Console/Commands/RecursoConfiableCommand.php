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
        //Logística y maniobras CAVA
        $clientCAVA = Client::where('name', 'Logística y maniobras CAVA')->first();
        //dd($clientCAVA);
        if ($clientCAVA) {
            $rcController= new RcController();
            $rcController->RCServiceUnits($gpsService, $clientCAVA);
            
        } else {
            $this->error('Cliente Logística y maniobras CAVA no encontrado.');
            return;
        }

        $clientHector = Client::where('name', 'Hector Manuel Orozco RecursoConfiable')->first();
        if ($clientHector) {
            $rcController= new RcController();
            $rcController->RCServiceUnits($gpsService, $clientHector);
            
        } else {
            $this->error('Cliente Hector Manuel Orozco RecursoConfiable no encontrado.');
            return;
        }
        $this->info('Comando ejecutado correctamente para recurso confiable.');
    }
}
