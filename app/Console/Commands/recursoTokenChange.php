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
        //Logística y maniobras CAVA
        $clientCAVA = Client::where('name', 'LOGISTICA Y MANIOBRAS CAVA')->where('company_id','>',0)->first();
        if ($clientCAVA) {
            $rcController= new RcController();
            $rcController->RCServiceLogin($gpsService, $clientCAVA);
            
        } else {
            $this->error('Cliente Logística y maniobras CAVA no encontrado.');
            return;
        }

        //$client = Client::where('name', $name);
       $clientHector = Client::where('name', 'Hector Manuel Orozco')->where('company_id','>',0)->first();
        if ($clientHector) {
            $rcController= new RcController();
        $rcController->RCServiceLogin($gpsService, $clientHector);
            
        } else {
            $this->error('Cliente Hector Manuel Orozco no encontrado.');
            return;
        }
        $this->info('Comando Login ejecutado correctamente para recurso confiable.');
        
    }
}
