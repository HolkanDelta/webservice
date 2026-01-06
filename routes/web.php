<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\sdk\sdkMapon;
use App\Http\Controllers\sdk\sdkfleet;
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
    Route::get('login-fleet/{clientId}', [sdkfleet::class, 'login'])->name('login-fleet');
    Route::get('fleet-tracking/{clientId}', [sdkfleet::class, 'tracking'])->name('fleet-tracking');
});

require __DIR__.'/settings.php';
