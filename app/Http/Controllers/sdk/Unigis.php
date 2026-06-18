<?php

namespace App\Http\Controllers\sdk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UnigisService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\sdk\sdkMapon;
use App\Models\Client;

class Unigis extends Controller
{
    public function UnigisLogin(UnigisService $unigisService, $client)
    {
        $result = $unigisService->callMethod('Login', [
            'SystemUser' => $client->user_name,
            'Password' => $client->user_pass,
        ]);
        return response()->json($result);
    }
    public function UnigisEvent(UnigisService $unigisService, $client)
    {
        // making a new instance of sdkMapon
        $unitslistsdk = new sdkMapon();
        $sdkMapon = new sdkMapon();
        // get a units list from mappon

        $unitslist = $unitslistsdk->units($client->apikey);
        $units = json_decode($unitslist->getContent());
        // declare arrays
        $unitsArray = [];
        $payload_data = [];
        //populate arrays
        foreach ($units->data->units as $unit) {
            $unitsArray[] = $unit->unit_id;
        }
        foreach ($unitsArray as $unit_id) {
            $unitsind = $sdkMapon->units_id($client->apikey, $unit_id);
            $unitsind = json_decode($unitsind->getContent());
            foreach ($unitsind->data->units as $unit) {
                $imei = (string) ($unit->device?->imei ?? $unit->vin);
                $payload_data["pEvento"][] = [
                                    'Dominio' => $unit->number ? $unit->number : $unit->label,
                                    'NroSerie' => '-1',
                                    'Codigo' =>  '01',
                                    'Latitud' => $unit->lat ? (string) $unit->lat : '0',
                                    'Longitud' => $unit->lng ? (string) $unit->lng : '0',
                                    'Altitud' => $unit->altitude ? (string) $unit->altitude : '0',
                                    'Velocidad' => $unit->speed != "null" ? (string) $unit->speed : '0',
                                    'FechaHoraEvento' => $unit->created_at ? (string) $unit->created_at : '0',
                                    'FechaHoraRecepcion' => $unit->last_update ? (string) $unit->last_update : '0.00'                          
                    ];  
            }
        }


        $result = $unigisService->callMethod('LoginYInsertarEventos', [
            'SystemUser' => $client->user_name,
            'Password' => $client->user_pass,
            'Eventos' => $payload_data,
        ]);
        return response()->json($result);
    }
}