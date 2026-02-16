<?php

namespace App\Http\Controllers\sdk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class sdkMapon extends Controller
{
    public function units($key)
    {
       $response = Http::get('https://acceso.holkan.com.mx/api/v1/unit/list.json', [
            'key' => $key,
        ]);
        return response()->json($response->json());
    }
}
