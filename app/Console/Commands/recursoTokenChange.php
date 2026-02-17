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
       $client = Client::where('name', 'Hector Manuel Orozco RecursoConfiable')->first();
        if ($client) {
            $rcController= new RcController();
        $rcController->RCServiceLogin($gpsService, $client);
            
        } else {
            $this->error('Cliente Hector Manuel Orozco RecursoConfiable no encontrado.');
            return;
        }
        $this->info('Comando Login ejecutado correctamente para recurso confiable.');
        
    }
}
