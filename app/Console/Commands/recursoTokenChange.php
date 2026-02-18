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

        $clientNames = [
            'LOGISTICA Y MANIOBRAS CAVA',
            'Hector Manuel Orozco',
            'Transportes Terrestres Vazquez',
        ];

        foreach ($clientNames as $name) {
            $client = Client::where('name', $name)->where('company_id','>=',0)->first();
            if ($client) {
                $rcController= new RcController();
                $rcController->RCServiceLogin($gpsService, $client);
            } else {
                $this->error('Cliente ' . $name . ' no encontrado.');
            }
        }
        
        $this->info('Comando Login ejecutado correctamente para recurso confiable.');
        
    }
}
