<?php

namespace App\Services;

use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;

use Exception;

class KronhTrackerService
{
    protected $endpoint = 'https://kws.kronh.com/TrackerWebServices/gps.asmx?';
    protected $lastResponse = null;

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    public function sendPositions(array $positions, $client)
    {
        //dd($positions);
        try {
            $innerXml = "<ArrayOfPositions>";
            
            foreach ($positions as $pos) {
                $innerXml .= "<Position>";
                $innerXml .= "<DeviceID>{$pos['DeviceID']}</DeviceID>";
                $innerXml .= "<DeviceAlias>{$pos['DeviceAlias']}</DeviceAlias>";
                $innerXml .= "<Date>{$pos['Date']}</Date>";
                $innerXml .= "<Time>{$pos['Time']}</Time>"; // Recordar que debe ser GMT=0
                $innerXml .= "<Latitude>{$pos['Latitude']}</Latitude>";
                $innerXml .= "<Longitude>{$pos['Longitude']}</Longitude>";
                $innerXml .= "<IgnitionStatus>{$pos['IgnitionStatus']}</IgnitionStatus>";
                $innerXml .= "<Speed>{$pos['Speed']}</Speed>";
                $innerXml .= "<Course>{$pos['Course']}</Course>";
                $innerXml .= "<TempFrozen>{$pos['TempFrozen']}</TempFrozen>";
                $innerXml .= "<TempCold>{$pos['TempCold']}</TempCold>";
                $innerXml .= "<EventNumber>{$pos['EventNumber']}</EventNumber>";
                $innerXml .= "</Position>";
            }
            
            $innerXml .= "</ArrayOfPositions>";
 
            $escapedPositionsList = htmlspecialchars($innerXml, ENT_XML1, 'UTF-8');

            $soapEnvelope = trim('<?xml version="1.0" encoding="utf-8"?>
                <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
                <soap12:Body>
                    <ExternalGPSInputs_V3 xmlns="https://kws.kronh.com/TrackerWebServices/gps.asmx">
                    <User>' . $client->user_name . '</User>
                    <Password>' . $client->user_pass . '</Password>
                    <PositionsList>' . $escapedPositionsList . '</PositionsList>
                    </ExternalGPSInputs_V3>
                </soap12:Body>
                </soap12:Envelope>');
          
            
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/soap+xml; charset=utf-8',
            ])->send('POST', $this->endpoint, [
                'body' => $soapEnvelope
            ]);

            $responseBody = $response->body();
            $this->lastResponse = $responseBody;

            $payload_data = $positions; 
            $resultado = $responseBody;

            if (str_contains($responseBody, '<ExternalGPSInputs_V3Result>-1</ExternalGPSInputs_V3Result>')) {
                $log_error = json_encode([
                    'error' => 'Error de autenticación (-1)',
                    'payload' => $positions,
                    'respuesta_servidor' => $responseBody
                ]);
                Log::channel('kronh')->error("KRONH: " . $log_error);
                return -1;
                
            } elseif (str_contains($responseBody, '<ExternalGPSInputs_V3Result>-3</ExternalGPSInputs_V3Result>')) {
                $log_error = json_encode([
                    'error' => 'Error de formato en PositionsList (-3)',
                    'payload' => $positions,
                    'respuesta_servidor' => $responseBody
                ]);
                Log::channel('kronh')->error("KRONH: " . $log_error);
                return -3;
                
            } elseif (str_contains($responseBody, '<ExternalGPSInputs_V3Result>0</ExternalGPSInputs_V3Result>')) {
                $log_error = json_encode([
                    'error' => 'Error en el guardado de datos (0)',
                    'payload' => $positions,
                    'respuesta_servidor' => $responseBody
                ]);
                Log::channel('kronh')->error("KRONH: " . $log_error);
                return 0;
                
            } elseif (str_contains($responseBody, '<ExternalGPSInputs_V3Result>1</ExternalGPSInputs_V3Result>')) {
                // ¡Caso de éxito real!
                $log_resultado = json_encode([
                    'mensaje' => 'Posiciones enviadas y guardadas correctamente (1)',
                    'payload' => $positions,
                    'resultado' => $responseBody,
                ]);
                Log::channel('kronh')->info("KRONH Exito: " . $log_resultado);
                return 1; 
            }

            Log::channel('kronh')->warning("KRONH Respuesta desconocida: " . $responseBody);
            return false;

        } catch (Exception $e) {
            Log::channel('kronh')->error("Error al enviar datos a KRONH: " . $e->getMessage());
            return false;
        }
    }
}