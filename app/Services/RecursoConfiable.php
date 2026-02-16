<?php

namespace App\Services;

use SoapClient;
use SoapFault;
use Illuminate\Support\Facades\Log;

class RecursoConfiable
{
    protected $client;
    protected $wsdl = "https://gps.rcontrol.com.mx/Tracking/wcf/RCService.svc?singleWsdl";

    public function __construct()
    {
        // Configuración básica para WCF
        $options = [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE, // Recomendado durante desarrollo
            'keep_alive' => false,
            // Si el servicio requiere usuario/pass en cabecera HTTP Basic Auth:
            // 'login' => env('GPS_USER'),
            // 'password' => env('GPS_PASS'),
        ];

        try {
            $this->client = new SoapClient($this->wsdl, $options);
        } catch (SoapFault $e) {
            Log::error("Error conectando a WSDL: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ejemplo genérico para llamar cualquier método del servicio
     */
    public function callMethod($methodName, $params = [])
    {
        try {
            // En WCF, a menudo los parámetros deben ir envueltos en un objeto
            // cuyo nombre coincide con el método.
            // Ejemplo: $response = $this->client->ObtenerUbicacion(['id' => 1]);
            
            $response = $this->client->__soapCall($methodName, [$params]);
            return $response;

        } catch (SoapFault $e) {
            // Capturar XML de la petición para depuración
            Log::error("SOAP Error en $methodName: " . $e->getMessage());
            Log::info("Last Request: " . $this->client->__getLastRequest());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /* * Implementa aquí métodos específicos según lo que viste en el Paso 2
     * Ejemplo hipotético:
     */
    public function getTrackingData($deviceId)
    {
        // Suponiendo que el método se llama 'GetTracking'
        return $this->callMethod('GetTracking', ['deviceId' => $deviceId]);
    }
}