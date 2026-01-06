<?php

namespace App\Http\Controllers\sdk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class sdkMapon extends Controller
{
    public function units()
    {
       $response = Http::get('https://acceso.holkan.com.mx/api/v1/unit/list.json', [
            'key' => 'd2a5b6f3b82ce76a4a5f7d2fb8b7666fcc6fa368',
        ]);

        // Puedes obtener el cuerpo de la respuesta como una cadena:
        //echo $response->body();
        return response()->json($response->json());
    }
}
