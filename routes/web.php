<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\sdk\sdkMapon;
use App\Http\Controllers\sdk\sdkfleet;
use App\Http\Controllers\sdk\RcController;
use App\Http\Controllers\sdk\controlT;
use App\Http\Controllers\sdk\Landstar;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::resource(name:'clientes', controller:ClientController::class);
    Route::resource(name:'servicios', controller:ServiceController::class);
    Route::get('units', [sdkMapon::class, 'units'])->name('units');
    //Fleet Rocket
    Route::get('login-fleet/{clientId}', [sdkfleet::class, 'login'])->name('login-fleet');
    Route::get('fleet-tracking/{clientId}', [sdkfleet::class, 'tracking'])->name('fleet-tracking');
    //Rcontrol
    Route::get('test-rcontrol', function () {
        $wsdl = "https://gps.rcontrol.com.mx/Tracking/wcf/RCService.svc?singleWsdl";
        $opts = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
            'trace' => true, // Permite ver las peticiones/respuestas crudas
            'exceptions' => true,
        ];

        try {
            $client = new \SoapClient($wsdl, $opts);
            // Esto imprimirá todas las funciones disponibles
            dd($client->__getFunctions(), $client->__getTypes());
        } catch (\SoapFault $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
    Route::get('rcservice-login', [RcController::class, 'RCServiceLogin'])->name('rcservice-login');
    //Landstar
    Route::get('test-landstar', function () {
        $wsdl = "https://compass-landstar.centralus.cloudapp.azure.com/locations/locationReceiver.wsdl";
        $opts = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
            'trace' => true, // Permite ver las peticiones/respuestas crudas
            'exceptions' => true,
        ];

        try {
            $client = new \SoapClient($wsdl, $opts);
            // Esto imprimirá todas las funciones disponibles
            dd($client->__getFunctions(), $client->__getTypes());
        } catch (\SoapFault $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
    Route::get('landstar-login', [Landstar::class, 'landstarauth'])->name('landstar-login');
    // controlT
    Route::get('controlT-login/{clientId}', [controlT::class, 'login'])->name('controlT-login');
    Route::get('controlT-tracking/{clientId}', [controlT::class, 'tracking'])->name('controlT-tracking');
});

require __DIR__.'/settings.php';
