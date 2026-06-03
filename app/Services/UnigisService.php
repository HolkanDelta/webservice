<?php

namespace App\Services;

use SoapClient;
use SoapFault;
use Illuminate\Support\Facades\Log;

class UnigisService
{
    protected $client;
    protected $wsdl = "https://cloud-test.unigis.com/hub_TEST/mapi/soap/gps/service.asmx?wsdl";

    public function __construct()
    {
        
        $options = [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE, 
            'keep_alive' => false,
        ];

        try {
            $this->client = new SoapClient($this->wsdl, $options);
        } catch (SoapFault $e) {
            Log::error("Error conectando a WSDL: " . $e->getMessage());
            throw $e;
        }
    }

    
    public function callMethod($methodName, $params = [])
    {
        try {
            
            $response = $this->client->__soapCall($methodName, [$params]);
            return $response;

        } catch (SoapFault $e) {
           Log::error("SOAP Error en $methodName: " . $e->getMessage());
            Log::info("Last Request: " . $this->client->__getLastRequest());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    
    public function getTrackingData($deviceId)
    {
        return $this->callMethod('GetTracking', ['deviceId' => $deviceId]);
    }
}