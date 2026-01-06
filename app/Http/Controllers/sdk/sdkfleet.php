<?php

namespace App\Http\Controllers\sdk;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\sdk\sdkMapon;

class sdkfleet extends Controller
{
    public function login($clientId)
    {
        $client = Client::whereId($clientId)->first();
        if ($client){

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://my.fleetrocket.io/v1/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "email": "holkan.murillo@fleetrocket.io", 
                "force": true, 
                "password": "bmOC7ePrYSa6&@cy", 
                "role": "API_TRACKING" 
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            
            $response = json_decode($response, true);
            try {
                 $client->token = $response['token'];
                 $client->save();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Login failed', 'message' => $e->getMessage()], 500);
            }
            return response()->json($response);

        } else {
            return response()->json(['error' => 'Client not found'], 404);
        }
    }

    public function tracking($clientId)
    {
        $client = Client::whereId($clientId)->first();
        if ($client){
            $units = new sdkMapon();
            $units = $units->units();
            $units = json_decode($units->getContent());
          // dd($units->data->units);
            foreach ($units->data->units as $unit) {

                $payload_data = [
                    "equipment" => [
                        [
                            "city" => "", 
                            "country" => $unit->country_code,
                            "date_time" => $unit->last_update, 
                            "latitude" => $unit->lat, 
                            "longitude" => $unit->lng, 
                            "postal_code" => "", 
                            "speed" => $unit->speed ?: 0, 
                            "state" => $unit->state->name, 
                            "tractor" => $unit->label, 
                            "tractor_plates" => $unit->number, 
                            "Trailer" => "", 
                            "trailer_plates" => "" 
                        ]
                    ]
                ];
                $equipment = json_encode($payload_data);
                $curl = curl_init();

                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://my.fleetrocket.io/v1/tracking',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $equipment,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: BEARER '.$client->token,
                ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                return response()->json(json_decode($response, true));
            }
            
            
        }else {
            return response()->json(['error' => 'Client not found'], 404);
        }
    }
}

