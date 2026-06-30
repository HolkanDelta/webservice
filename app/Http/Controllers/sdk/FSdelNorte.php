<?php

namespace App\Http\Controllers\sdk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\sdk\sdkMapon;
use App\Models\Client;

class FSdelNorte extends Controller
{
    /**
     * Sincroniza la ubicación de las unidades de Mapon con el Webhook de FSdelNorte.
     *
     * @param int $clientId
     * @return array
     * @throws \Exception
     */
    public function tracking($clientId)
    {
        $client = Client::whereId($clientId)->first();
        if (!$client) {
            throw new \Exception("Client not found");
        }

        $unitslistsdk = new sdkMapon();
        $sdkMapon = new sdkMapon();
        $unitslist = $unitslistsdk->units($client->apikey);
        $units = json_decode($unitslist->getContent());

        if (empty($units->data->units)) {
            return [
                'payload' => [],
                'resultado' => 'No units found'
            ];
        }

        $unitsArray = [];
        foreach ($units->data->units as $unit) {
            $unitsArray[] = $unit->unit_id;
        }

        $payload_data = [];
        foreach ($unitsArray as $unit_id) {
            $unitsind = $sdkMapon->units_id($client->apikey, $unit_id);
            $unitsind = json_decode($unitsind->getContent());
            foreach ($unitsind->data->units as $unit) {
                // Formatear timestamp en ISO 8601 UTC
                // Mapon almacena last_update en America/Mexico_City
                $timestamp = Carbon::parse($unit->last_update, 'America/Mexico_City')
                    ->setTimezone('UTC')
                    ->format('Y-m-d\TH:i:s\Z');

                $payload_data[] = [
                    'timestamp' => $timestamp,
                    'economico' => (string) ($unit->label ?? $unit->number),
                    'placa' => (string) ($unit->number ?? $unit->label),
                    'latitude' => (float) $unit->lat,
                    'longitude' => (float) $unit->lng,
                    'speed_kmh' => (int) ($unit->speed ?? 0),
                    'course' => (int) ($unit->direction ?? 0),
                    'engine_state' => $unit->state?->name === 'driving',
                    'event' => 1 // TelemetryEvent enum (1 = reporte normal / ping)
                ];
            }
        }

        if (empty($payload_data)) {
            return [
                'payload' => [],
                'resultado' => 'No position data found for units'
            ];
        }

        $response = Http::withToken($client->token)
            ->post('https://gps.fsdelnorte.com/api/v1/webhooks/gps', $payload_data);

        $log_resultado_arr = [
            'payload' => $payload_data,
            'resultado' => $response->body(),
        ];

        Log::channel('fsdelnorte')->info(json_encode($log_resultado_arr));

        if ($response->successful()) {
            return $log_resultado_arr;
        }

        throw new \Exception("Error al enviar a FSdelNorte: " . $response->body());
    }
}
