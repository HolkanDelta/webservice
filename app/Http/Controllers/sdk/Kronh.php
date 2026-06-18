<?php

namespace App\Http\Controllers\sdk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\sdk\sdkMapon; 
use App\Services\KronhTrackerService;
use Illuminate\Support\Carbon;
use App\Models\Client;

class Kronh extends Controller
{
    public function tracking(KronhTrackerService $kronhService, $clientid )
    {
        $client = Client::whereId($clientid)->first();
        $unitslistsdk = new sdkMapon();
        $sdkMapon = new sdkMapon();
        $unitslist = $unitslistsdk->units($client->apikey);
        $units = json_decode($unitslist->getContent());
        $unitsArray = [];
        $payload_data = [];
        $posiciones = [];
       
        foreach ($units->data->units as $unit) {
            $unitsArray[] = $unit->unit_id;
        }
        //dd( $units->data->units );
        $nowUtc = Carbon::now('UTC');
        foreach ($unitsArray as $unit_id) {            
            $unitsind = $sdkMapon->units_id($client->apikey, $unit_id);
            $unitsind = json_decode($unitsind->getContent());
            foreach ($unitsind->data->units as $unit) {
                $driving = $unit->state->name;
                $imei = (string) ($unit->device?->imei ?? $unit->vin);
                $posiciones[] = [                    
                        'DeviceID'       => $imei,
                        'DeviceAlias'    => (string) $unit->number,
                        'Date'           => $nowUtc->format('Y-m-d'),
                        'Time'           => $nowUtc->format('H:i:s'),
                        'Latitude'       => (string) $unit->lat,
                        'Longitude'      => (string) $unit->lng,
                        'IgnitionStatus' => 'true',
                        'Speed'          => $unit->speed ? $unit->speed : 0,
                        'Course'         => $unit->direction,
                        'TempFrozen'     => 'NA',
                        'TempCold'       => 'NA',
                        'EventNumber'    => '110' // 110 = Reporte normal
                ];                
            }
        }
        $resultado = $kronhService->sendPositions($posiciones, $client);

        $rawResponse = $kronhService->getLastResponse();

        if ($resultado === 1) {
            return [
                'payload' => $posiciones,
                'resultado' => $rawResponse ?: 'Posiciones enviadas y guardadas correctamente (1)'
            ];
        }

        throw new \Exception("Hubo un error al enviar los datos a KRONH. Respuesta: " . ($rawResponse ?: 'Sin respuesta.'));
    }
}