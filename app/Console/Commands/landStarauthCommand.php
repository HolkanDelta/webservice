<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\sdk\Landstar;
use App\Models\Client;
use App\Services\LandstarService;

class landStarauthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:land-starauth-command';

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
        $clientNames = [
            'Holkan Mapon-Landstar 1',
        ];

        foreach ($clientNames as $name) {
            $client = Client::where('name', $name)->where('company_id','>=',0)->first();
            if ($client) {
                $rcController= new Landstar();
                $rcController->landstarauth($gpsService, $client);
            } else {
                $this->error('Cliente ' . $name . ' no encontrado.');
            }
        }
        
        $this->info('Comando Login ejecutado correctamente para landstar.');
        
    }
}
