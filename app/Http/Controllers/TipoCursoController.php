<?php

namespace App\Http\Controllers;

use App\Http\Requests\TipoCursoRequest;
use App\Models\TipoCurso;
use App\Models\TipoVehiculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TipoCursoController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $tiposCurso = TipoCurso::with('tipoVehiculo')
            ->when($search, fn ($query, $search) => $query->where('nombre', 'ilike', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('TiposCurso/Index', [
            'tiposCurso' => $tiposCurso,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('TiposCurso/Create', [
            'tiposVehiculo' => TipoVehiculo::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(TipoCursoRequest $request): RedirectResponse
    {
        TipoCurso::create($request->validated());

        return redirect()->route('tipos-curso.index')->with('status', 'Tipo de curso creado correctamente.');
    }

    public function edit(TipoCurso $tipoCurso): Response
    {
        return Inertia::render('TiposCurso/Edit', [
            'tipoCurso' => $tipoCurso,
            'tiposVehiculo' => TipoVehiculo::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function update(TipoCursoRequest $request, TipoCurso $tipoCurso): RedirectResponse
    {
        $tipoCurso->update($request->validated());

        return redirect()->route('tipos-curso.index')->with('status', 'Tipo de curso actualizado correctamente.');
    }

    public function destroy(TipoCurso $tipoCurso): RedirectResponse
    {
        // No se elimina de la base de datos: se da de baja (estado_curso=inactivo)
        // para no perder el historial de cursos ya generados con este tipo.
        $tipoCurso->update(['estado_curso' => 'inactivo']);

        return redirect()->route('tipos-curso.index')->with('status', 'Tipo de curso dado de baja correctamente.');
    }
}
