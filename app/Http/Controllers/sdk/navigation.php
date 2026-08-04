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

class navigation extends Controller
{
    public function login($clientId)
    {
        $client = Client::whereId($clientId)->first();
        $base_url = "https://api-v3.navigation.com.mx";
        $url = $base_url . "/api/v1/users/login";
        $user = $client->user_name;
        $password = $client->user_pass;
        $idnav = $client->company_id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "usuario" => $user,
            "password" => $password,
            "cliente_admin" => $idnav
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $token = json_decode($response);
        if (isset($token->token)) {
            $client->token = $token->token;
            $client->save();
        } elseif (isset($token->access_token)) {
            $client->token = $token->access_token;
            $client->save();
        } else {
            Log::channel('Navigation')->error('Login failed in navigation sdk: ' . $response);
        }
        return $response;
    }

    public function tracking($clientid)
    {
        $all_results = [];
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
        $base_url = "https://api-v3.navigation.com.mx";
        foreach ($unitsArray as $unit_id) {
            $unitsind = $sdkMapon->units_id($client->apikey, $unit_id);
            $unitsind = json_decode($unitsind->getContent());

            foreach ($unitsind->data->units as $unit) {
                $imei = (string) ($unit->device?->imei ?? $unit->vin);
                $dateEventAVL = Carbon::now('America/Mexico_City');
                $dateEventAVL->utc();

                $body = [
                    'unitID' => $imei,
                    'idMensaje' => 0,
                    'registro' => $dateEventAVL->format('Y-m-d\TH:i:s.u\Z'),
                    'tipomsn' => 1,
                    'marcaGPS' => "Suntech",
                    'modeloGPS' => "ST4000",
                    'ignicion' => true,
                    'latitud' =>  (string) $unit->lat,
                    'longitud' => (string) $unit->lng,
                    'velocidad' => $unit->speed ?? 0,
                    'odometro' => $unit->mileage ? (float) $unit->mileage : 0.00,
                    'entrada1sDigital' => [true, true, true, false],
                    'salidasDigital' => [true, true, true, false],
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $client->token,
                ])->post($base_url . "/api/v1/unidades/ws/send", $body);

                if ($response->status() === 401 || (is_array($response->json()) && isset($response->json()['data']) && $response->json()['data'] === 'Unauthorized')) {
                    $this->login($clientid);
                    $client = Client::whereId($clientid)->first();
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $client->token,
                    ])->post($base_url . "/api/v1/unidades/ws/send", $body);
                }

                $log_resultado_arr = [
                    'payload' => $body,
                    'resultado' => $response->body(),
                ];

                Log::channel('Navigation')->info(json_encode($log_resultado_arr));
                $all_results[] = $log_resultado_arr;
            };
        };
        return $all_results;
    }
}
