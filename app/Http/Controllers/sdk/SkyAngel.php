<?php

namespace App\Http\Controllers\Sdk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\sdk\sdkMapon; 

class SkyAngel extends Controller
{
    public function skyangelUnits( $client )
    {
        $unitslistsdk = new sdkMapon();
        $sdkMapon = new sdkMapon();
        $unitslist = $unitslistsdk->units($client->apikey);
        $units = json_decode($unitslist->getContent());
        $base_url = "http://api.skyangel.com.mx:8081/";
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
                $fechaHora = Carbon::parse($unit->last_update, 'America/Mexico_City')->format('Y-m-d H:i:s P');
                $payload_data = [
                    'usuario' => $client->user_name, 
                    'password' => $client->user_pass, 
                    'imei' => (string) $unit->device->imei, 
                    'neconomico' => $unit->number, 
                    'fechahora' => $fechaHora,
                    'latitud' => $unit->lat,
                    'longitud' => $unit->lng,
                    'altitud' => "",
                    'velocidad' => $unit->speed,
                    'direccion' => $unit->direction,
                    'ubicación' => "",
                    'evento' => "",
                    'temperatura' => "",
                    'gasolina' => ""                  
                ];
                $response = Http::post('http://api.skyangel.com.mx:8081/insertaMov/', $payload_data);
                $log_resultado = json_encode(
                    [
                        'payload' => $payload_data,
                        'resultado' => $response->body(),
                    ]
                );
                Log::channel('skyangel')->info($log_resultado);
                
            } 
        }

    }
}
