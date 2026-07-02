<?php

namespace App\Http\Controllers;

use App\Http\Requests\FranjaHorariaRequest;
use App\Models\FranjaHoraria;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FranjaHorariaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('FranjasHorarias/Index', [
            'franjasHorarias' => FranjaHoraria::orderBy('hora_inicio')->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('FranjasHorarias/Create');
    }

    public function store(FranjaHorariaRequest $request): RedirectResponse
    {
        FranjaHoraria::create($request->validated());

        return redirect()->route('franjas-horarias.index')->with('status', 'Franja horaria creada correctamente.');
    }

    public function edit(FranjaHoraria $franjaHoraria): Response
    {
        return Inertia::render('FranjasHorarias/Edit', ['franjaHoraria' => $franjaHoraria]);
    }

    public function update(FranjaHorariaRequest $request, FranjaHoraria $franjaHoraria): RedirectResponse
    {
        $franjaHoraria->update($request->validated());

        return redirect()->route('franjas-horarias.index')->with('status', 'Franja horaria actualizada correctamente.');
    }

    public function destroy(FranjaHoraria $franjaHoraria): RedirectResponse
    {
        try {
            $franjaHoraria->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'No se puede eliminar: hay cursos asociados a esta franja horaria.');
        }

        return redirect()->route('franjas-horarias.index')->with('status', 'Franja horaria eliminada correctamente.');
    }
}
