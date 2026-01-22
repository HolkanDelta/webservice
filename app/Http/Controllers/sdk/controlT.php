<?php

namespace App\Http\Controllers\sdk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class controlT extends Controller
{
    public function login($clientid)
    {
        $client = Client::whereId($clientId)->first();
        $base_url = "https://hub.controlt.com.co";
        $url = $base_url . "/Account/Auth";        
        $user = $client->user_name;
        $password = $client->user_pass;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "user" => $user,
            "password" => $password
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function tracking($clientid)
    {
        $token = $this->login($clientid);
        $base_url = "https://hub.controlt.com.co";
        $url = $base_url . "/Account/Auth";
    }
    
}
