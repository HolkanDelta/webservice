<?php

namespace App\Http\Controllers\sdk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Http\Controllers\sdk\sdkMapon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class controlT extends Controller
{
    public function login($clientId) 
    {
        $client = Client::whereId($clientId)->first();
        $base_url = "https://hub.controlt.com.co";
        $url = $base_url . "/Account/Auth";        
        $user = $client->user_name;
        $password = $client->user_pass;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "username" => $user,
            "password" => $password
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $token = json_decode($response);
        $client->token = $token->access_token;
        $client->save();
        return $response;
    }

    public function tracking($clientid)
    {
        //$token = json_decode( $this->login($clientid));
        $client = Client::whereId($clientid)->first();
        $unitslistsdk = new sdkMapon();
        $sdkMapon = new sdkMapon();
        $unitslist = $unitslistsdk->units($client->apikey);
        $units = json_decode($unitslist->getContent());
        $unitsArray = [];
        foreach ($units->data->units as $unit) {
            $unitsArray[] = $unit->unit_id;
        }
        $base_url = "https://hub.controlt.com.co";
        foreach ($unitsArray as $unit_id) {            
            $unitsind = $sdkMapon->units_id($client->apikey, $unit_id);
            $unitsind = json_decode($unitsind->getContent());

            foreach ($unitsind->data->units as $unit) {
                //construye el json inicial para hacerlo base64
                $initarr = [
                    'Serial' => $unit->device->imei,
                    'Status' => 1,
                    'Priority' => 0,
                    'Velocity' => (int) ($unit->speed ?? 0),
                    'Odometer' => (int) ($unit->mileage ?? 0),
                    'Ignition' => $unit?->state?->name === "driving",
                    'Battery'  => 0,
                    'Altitude' => 0,
                    'Course' => "null",
                    'Movil' => 0,
                    'Temperature1' => '',
                    'Temperature2' => '',
                    'City' => "null",
                    'Department' => "null",
                    'Address' => "null null 0",
                ];
                $nodoData = base64_encode(json_encode($initarr));

                $dateEventGPS = Carbon::parse($unit->last_update)->setTimezone('America/Bogota');
                $dateEventAVL = Carbon::parse($unit->created_at)->setTimezone('America/Bogota');

                $body = [
                    'licensePlate' => substr($unit->number, 0, 6),
                    'latitude' => $unit->lat,
                    'longitude' => $unit->lng,
                    'dateEventGPS' => $dateEventGPS->format('Y-m-d H:i:s'),
                    'dateEventAVL' => $dateEventAVL->format('Y-m-d H:i:s'),
                    'typeEvent' => "01",
                    'codeEvent'  => $unit->type,
                    'descriptionEvent'  => $unit->vin,
                    'username' => $client->user_name,
                    'data' => $nodoData,                    
                ];

                $response = Http::withHeaders([
                    'Authorization' => $client->token, // Reemplaza los puntos por tu token real
                ])->post('https://hub.controlt.com.co/Register/Insert', $body);
                
                
                $log_resultado = json_encode(
                    [
                        'payload' => $body,
                        'resultado' => $response->body(),
                    ]
                );

                Log::channel('controlT')->info($log_resultado);              

            };
        };
        return "Las unidades fueron transferidas a control T";
        
    }
    
}
