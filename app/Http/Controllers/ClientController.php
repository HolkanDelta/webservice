<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Services\RecursoConfiable;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('clients/index', [
            'clients' => Client::with('services')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('clients/create', [
            'services' => \App\Models\Service::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        $client = Client::create($request->validated());
        $client->services()->sync($request->input('services', []));
        return redirect()->route('clientes.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $cliente)
    {
        $cliente->load('services');
        return Inertia::render('clients/show', [
            'cliente' => $cliente,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $cliente)
    {
        $cliente->load('services');
        return Inertia::render('clients/edit', [
            'cliente' => $cliente,
            'services' => \App\Models\Service::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $cliente)
    {
        $cliente->update($request->validated());
        $cliente->services()->sync($request->input('services', []));
        return redirect()->route('clientes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index');
    }

    


}
