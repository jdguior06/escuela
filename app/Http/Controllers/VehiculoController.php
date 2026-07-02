<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehiculoRequest;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehiculoController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $vehiculos = Vehiculo::with('tipoVehiculo')
            ->when($search, fn ($query, $search) => $query->where('placa', 'ilike', "%{$search}%")
                ->orWhere('marca', 'ilike', "%{$search}%")
                ->orWhere('modelo', 'ilike', "%{$search}%"))
            ->orderBy('placa')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Vehiculos/Index', [
            'vehiculos' => $vehiculos,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Vehiculos/Create', [
            'tiposVehiculo' => TipoVehiculo::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(VehiculoRequest $request): RedirectResponse
    {
        Vehiculo::create($request->validated());

        return redirect()->route('vehiculos.index')->with('status', 'Vehículo creado correctamente.');
    }

    public function edit(Vehiculo $vehiculo): Response
    {
        return Inertia::render('Vehiculos/Edit', [
            'vehiculo' => $vehiculo,
            'tiposVehiculo' => TipoVehiculo::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function update(VehiculoRequest $request, Vehiculo $vehiculo): RedirectResponse
    {
        $vehiculo->update($request->validated());

        return redirect()->route('vehiculos.index')->with('status', 'Vehículo actualizado correctamente.');
    }

    public function destroy(Vehiculo $vehiculo): RedirectResponse
    {
        // No se elimina de la base de datos: se da de baja (estado_vehiculo=inactivo)
        // para no perder el historial de cursos asociados.
        $vehiculo->update(['estado_vehiculo' => 'inactivo']);

        return redirect()->route('vehiculos.index')->with('status', 'Vehículo dado de baja correctamente.');
    }
}
