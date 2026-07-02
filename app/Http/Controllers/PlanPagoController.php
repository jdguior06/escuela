<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlanPagoRequest;
use App\Models\PlanPago;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanPagoController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $planesPago = PlanPago::when($search, fn ($query, $search) => $query->where('nombre', 'ilike', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('PlanesPago/Index', [
            'planesPago' => $planesPago,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('PlanesPago/Create');
    }

    public function store(PlanPagoRequest $request): RedirectResponse
    {
        PlanPago::create($request->validated());

        return redirect()->route('planes-pago.index')->with('status', 'Plan de pago creado correctamente.');
    }

    public function edit(PlanPago $planPago): Response
    {
        return Inertia::render('PlanesPago/Edit', ['planPago' => $planPago]);
    }

    public function update(PlanPagoRequest $request, PlanPago $planPago): RedirectResponse
    {
        $planPago->update($request->validated());

        return redirect()->route('planes-pago.index')->with('status', 'Plan de pago actualizado correctamente.');
    }

    public function destroy(PlanPago $planPago): RedirectResponse
    {
        // No se elimina de la base de datos: se da de baja (estado=inactivo)
        // para no perder el historial de inscripciones que ya lo usaron.
        $planPago->update(['estado' => 'inactivo']);

        return redirect()->route('planes-pago.index')->with('status', 'Plan de pago dado de baja correctamente.');
    }
}
