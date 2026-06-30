<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::create([
            'name' => 'MAPON - RECURSO_CONFIABLE',
            'description' => 'RECURSO CONFIABLE',
            'base_url' => 'http://gps.rcontrol.com.mx/Tracking/wcf/RCService.svc',
            'recurrence' => true,
        ]);
        Service::create([
            'name' => 'MAPON_CONTROL_T',
            'description' => 'ControlT',
            'base_url' => 'https://hub.controlt.com.co/',
            'recurrence' => true,
        ]);
        Service::create([
            'name' => 'MAPON_LOGITRACK',
            'description' => 'Logitrack',
            'base_url' => 'https://gps-homologations.logitrack.mx/integrations/api/v1',
            'recurrence' => true,
        ]);
        Service::create([
            'name' => 'MAPON - SKYANGEL',
            'description' => 'Skyangel',
            'base_url' => 'http://api.skyangel.com.mx:8081',
            'recurrence' => true,
        ]);
        Service::create([
            'name' => 'MAPON - PEGASUS',
            'description' => 'Pegasus',
            'base_url' => '',
            'recurrence' => true,
        ]);
        Service::create([
            'name' => 'MAPON - KRONH',
            'description' => 'Kronh',
            'base_url' => 'http://192.169.152.105/TrackerWebServices/gps.asmx',
            'recurrence' => true,
        ]);
        Service::create([
            'name' => 'MAPON - OVERHAUL',
            'description' => 'Overhaul',
            'base_url' => 'https://dev.int.over-haul.com/providers/iot-event/generic/v1',
            'recurrence' => true,
        ]);
        Service::create([
            'name' => 'MAPON_LANDSTAR',
            'description' => 'Landstar',
            'base_url' => 'https://compass-landstar.centralus.cloudapp.azure.com/locations/locationReceiver.wsdl',
            'recurrence' => true,
        ]);
        Service::create([
            'name' => 'MAPON_FLEET_ROCKET',
            'description' => 'Fleet Rocket',
            'base_url' => 'https://my.fleetrocket.io/v1',
            'recurrence' => true,
        ]);
        Service::create([
            'name' => 'MAPON_UNIGIS',
            'description' => 'Unigis',
            'base_url' => 'https://cloud-test.unigis.com/hub_TEST/mapi/soap/gps/service.asmx',
            'recurrence' => true,
        ]);
        Service::create([
            'name' => 'MAPON_FSDELNORTE',
            'description' => 'FSdelNorte',
            'base_url' => 'https://gps.fsdelnorte.com/api/v1/webhooks/gps',
            'recurrence' => true,
        ]);
    }
}
