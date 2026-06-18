<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use Inertia\Inertia;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('services/index', [
            'services' => Service::with('clients')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       return Inertia::render('services/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceRequest $request)
    {
        Service::create($request->validated());
        return redirect()->route('servicios.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $servicio)
    {
        $servicio->load('clients');
        return Inertia::render('services/show', [
            'servicio' => $servicio,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $servicio)
    {
        return Inertia::render('services/edit', [
            'servicio' => $servicio,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $servicio)
    {
        $servicio->update($request->validated());
        return redirect()->route('servicios.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $servicio)
    {
        $servicio->delete();
        return redirect()->route('servicios.index');
    }
}
