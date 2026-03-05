<?php

namespace App\Http\Controllers\Sdk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\sdk\sdkMapon;

class Pegasus extends Controller
{
    public function pegasussUnits( $client )
    {
        $unitslistsdk = new sdkMapon();
        $sdkMapon = new sdkMapon();
        $unitslist = $unitslistsdk->units($client->apikey);
        $units = json_decode($unitslist->getContent());
        $base_url = "https://pegasus248.peginstances.com/receivers/json";
        
        $headers = [
            'Authenticate' => '8b00d3783f2debd4be19e5461fdd19769e05f6bedeec36ff64d8fef6',
            'Content-Type' => 'application/json'
        ];

        $unitsArray = [];
        $payload_data = [];
       
        foreach ($units->data->units as $unit) {
            $unitsArray[] = $unit->unit_id;
        };

        foreach ($unitsArray as $unit_id) {
            $unitsind = $sdkMapon->units_id($client->apikey, $unit_id);
            $unitsind = json_decode($unitsind->getContent());
            foreach ($unitsind->data->units as $unit) {
                $driving = $unit->state->name;
                $unixTimestamp = Carbon::parse($unit->last_update, 'America/Mexico_City')->timestamp;
                $payload_data[] = [
                    'timestamp' => $unixTimestamp, 
                    'device.id' => $unit->device->imei, 
                    'position.latitude' => $unit->lat, 
                    'position.longitude' => $unit->lng,
                    'server.timestamp' => $unixTimestamp,
                    'device.name' => (string) $unit->number,
                    'position.direction' => $unit->direction,
                    'position.speed' => $unit->speed ? (string) $unit->speed : 0,
                    'position.valid' => true,
                    'event.enum' => "",
                    'event.label' => "",                
                    'metric.odometer' => $unit->mileage,
                    'protocol.id' => "rt.platform",
                    'device.type.id' => "rt.platform",
                    'adas.snapshots.count' => 1,
                    'adas.snapshot.timestamp' => $unixTimestamp     
                ];               
            }
        };


        $response = Http::withHeaders($headers)->post($base_url, $payload_data);
        
        $log_resultado = json_encode(
            [
                'payload' => $payload_data,
                'resultado' => $response->body(),
            ]
        );
        
        Log::channel('pegasus')->info($log_resultado);
    }
}
