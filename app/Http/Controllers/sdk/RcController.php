<?php

namespace App\Http\Controllers\sdk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RecursoConfiable;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\sdk\sdkMapon;

class RcController extends Controller
{
    public function RCServiceLogin(RecursoConfiable $gpsService): JsonResponse
    {
        $resultado = $gpsService->callMethod('GetUserToken', [
                    'userId' => 'ws_avl_holkan',
                    'password' => 'bGcU#584znHl#0',
                ]);
            return response()->json($resultado);
    }
    public function RCServiceUnits(RecursoConfiable $gpsService, $client): JsonResponse
    {
        $units = new sdkMapon();
        $units = $units->units($client->apikey);
        $units = json_decode($units->getContent());
        foreach ($units->data->units as $unit) {
            $payload_data = [
                    "event" => [
                        [
                            'code' => 0,
                            'asset' => $unit->number ? $unit->number : $unit->label,
                            'serialNumber' => $unit->vin ? $unit->vin : $unit->number,
                            'customer' => $client->company_id ? $client->company_id : NULL,
                            'lat' => $unit->lat ? $unit->lat : 0,
                            'lng' => $unit->lng ? $unit->lng : 0,
                            'date' => $unit->last_update ? $unit->last_update : null,
                            'speed' => $unit->speed ? $unit->speed : 0,
                            'ignition' => $unit->ignition_total_time ? $unit->ignition_total_time : NULL,
                            'battery' =>  '0',
                            'full_address' => '0',
                            'altitude' => '0',
                            'course' => $unit->direction ? $unit->direction : NULL,
                            'humidity' => '0',
                            'odometer' => $unit->mileage ? $unit->mileage : NULL,
                            'temperature' => '0',
                            'vehicleType' => $unit->type ? $unit->type : NULL,
                            'vehicleBrand' => '0',
                            'vehicleModel' => '0',
                            'shipment' => '0',
                        ]
                    ]
                ];
        }        
        
        
        $resultado = $gpsService->callMethod('GPSAssetTracking', [
                     'token' => $client->token,
                     'events' => $payload_data,
                 ]);
        //dd($resultado);
         return response()->json($resultado);
    }
}

     
     
     