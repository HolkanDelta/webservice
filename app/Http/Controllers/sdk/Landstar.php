<?php

namespace App\Http\Controllers\Sdk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LandstarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class Landstar extends Controller
{
    public function landstarauth (LandstarService $gpsService, $client): JsonResponse
    {
        $resultado = $gpsService->callMethod('GetUserToken', [
                    'userId' => $client->user_name,
                    'password' => $client->user_pass,
                ]);
        $token = $resultado->GetUserTokenResult->token;        
        $client->token = $token;
        $client->save();
        return response()->json($resultado);
    }
    public function landstarUnits(LandstarService $gpsService, $client): JsonResponse
    {
        $unitslistsdk = new sdkMapon();
        $sdkMapon = new sdkMapon();
        $unitslist = $unitslistsdk->units($client->apikey);
        $units = json_decode($unitslist->getContent());
        $unitsArray = [];
        $payload_data = [];
       
        foreach ($units->data->units as $unit) {
            $unitsArray[] = $unit->unit_id;
        }
        
        foreach ($unitsArray as $unit_id) {            
            $unitsind = $sdkMapon->units_id($client->apikey, $unit_id);
            $unitsind = json_decode($unitsind->getContent());
            foreach ($unitsind->data->units as $unit) {
                $driving = $unit->state->name;
                
                //if ($driving == "driving") {                    
                    //declara variable de customer
                    $company = [
                        'id' => $client->company_id,
                        'name' => $client->name,
                    ];
                    //construye payload
                    $payload_data["Event"][] = [
                                    'altitude' => '0',
                                    'asset' => $unit->number ? $unit->number : $unit->label,
                                    'battery' =>  '0',
                                    'code' => '0',
                                    'course' => '0',
                                    'customer' => $company,
                                    'date' => $unit->last_update ? (string) $unit->last_update : '0',
                                    'direction' => '0',
                                    'humidity' => '0.00',
                                    'ignition' => true,
                                    'latitude' => $unit->lat ?  $unit->lat : '0',
                                    'longitude' => $unit->lng ?  $unit->lng : '0',
                                    'odometer' => $unit->mileage ? (string) $unit->mileage : '0',
                                    'serialNumber' => $unit->device->imei ? (string) $unit->device->imei : (string) $unit->vin,
                                    'shipment' => '0',                          
                                    'speed' => $unit->speed ? (string) $unit->speed : '0',
                                    'temperature' => '0.00',
                                    'vehicleType' => $unit->type ? $unit->type : '0',
                                    'vehicleBrand' => '0',
                                    'vehicleModel' => '0',                            
                    ];                    
                //}                
            } 
        }
        //dd($payload_data);
        $date = date('Y-m-d H:i:s');
        $payload = json_encode($payload_data);
        //dd($payload);
        $resultado = $gpsService->callMethod('GPSAssetTracking', [
                     'token' => $client->token,
                     'events' => $payload_data,
                 ]);
                 //dd($resultado);
        $log_resultado = json_encode(
            [
                'payload' => $payload_data,
                'resultado' => $resultado,
            ]
        );
        //dd($log_resultado);
        Log::channel('landstar')->info($log_resultado);
        return response()->json($resultado);

    }

}
