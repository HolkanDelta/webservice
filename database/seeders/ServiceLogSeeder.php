<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceLog;
use Carbon\Carbon;

class ServiceLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = Service::all();

        if ($services->isEmpty()) {
            $this->command->warn('No hay servicios registrados para generar logs.');
            return;
        }

        $statuses = ['success', 'partial', 'failure'];
        $errorMessages = [
            'SoapFault: Could not connect to host',
            'Connection timed out after 30 seconds',
            'Authentication failed: invalid token provided',
            'Internal Server Error (500) from server endpoint',
            'WSDL loading error: document parsing failed'
        ];

        foreach ($services as $service) {
            $clients = $service->clients;
            $now = Carbon::now();
            
            for ($i = 49; $i >= 0; $i--) {
                $runTime = (clone $now)->subMinutes($i * 45); // execution every 45 mins

                // Determine status based on probabilities
                $rand = rand(1, 100);
                if ($rand <= 88) {
                    $status = 'success';
                    $message = 'Ejecutado correctamente para ' . $service->name;
                } elseif ($rand <= 96) {
                    $status = 'partial';
                    $message = 'Sincronización parcial. Errores detectados en algunos clientes.';
                } else {
                    $status = 'failure';
                    $message = $errorMessages[array_rand($errorMessages)];
                }

                // Construct structured payload
                $payload = [];
                $mockPayloadData = [
                    [
                        'DeviceID' => '869267074208179',
                        'DeviceAlias' => '11UK3E (85)',
                        'Latitude' => '25.72283',
                        'Longitude' => '-100.25773',
                        'Speed' => 0,
                        'Course' => 244
                    ]
                ];
                $mockResponseData = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope"><soap:Body><ExternalGPSInputs_V3Response xmlns="https://kws.kronh.com/gps.asmx"><ExternalGPSInputs_V3Result>1</ExternalGPSInputs_V3Result></ExternalGPSInputs_V3Response></soap:Body></soap:Envelope>';

                if ($clients->isEmpty()) {
                    $payload[] = [
                        'client' => 'Cliente Demo',
                        'status' => $status === 'failure' ? 'failure' : 'success',
                        'message' => $status === 'failure' ? $message : 'Sincronizado correctamente',
                        'transmission_payload' => $status === 'failure' ? null : $mockPayloadData,
                        'transmission_response' => $status === 'failure' ? 'SoapFault: Server connection failed' : $mockResponseData,
                    ];
                } else {
                    foreach ($clients as $index => $client) {
                        if ($status === 'success') {
                            $payload[] = [
                                'client' => $client->name,
                                'status' => 'success',
                                'message' => 'Sincronizado correctamente',
                                'transmission_payload' => $mockPayloadData,
                                'transmission_response' => $mockResponseData,
                            ];
                        } elseif ($status === 'partial') {
                            // First client fails, rest succeeds
                            $isFail = ($index === 0);
                            $payload[] = [
                                'client' => $client->name,
                                'status' => $isFail ? 'failure' : 'success',
                                'message' => $isFail ? 'Error de conexión: SoapFault' : 'Sincronizado correctamente',
                                'transmission_payload' => $isFail ? null : $mockPayloadData,
                                'transmission_response' => $isFail ? 'SoapFault: Server timeout' : $mockResponseData,
                            ];
                        } else {
                            $payload[] = [
                                'client' => $client->name,
                                'status' => 'failure',
                                'message' => $message,
                                'transmission_payload' => null,
                                'transmission_response' => $message,
                            ];
                        }
                    }
                }

                $runtimeMs = $status === 'failure' ? rand(100, 5000) : rand(300, 1800);

                ServiceLog::create([
                    'service_id' => $service->id,
                    'status' => $status,
                    'message' => $message,
                    'runtime_ms' => $runtimeMs,
                    'payload' => $payload,
                    'created_at' => $runTime,
                    'updated_at' => $runTime,
                ]);
            }
        }
    }
}
