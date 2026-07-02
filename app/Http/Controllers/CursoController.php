<?php

namespace App\Http\Controllers;

use App\Http\Requests\CursoRequest;
use App\Models\Curso;
use App\Models\FranjaHoraria;
use App\Models\TipoCurso;
use App\Models\Usuario;
use App\Models\Vehiculo;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CursoController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Cursos/Index', [
            'cursos' => Curso::with(['instructor', 'vehiculo', 'tipoCurso', 'franjaHoraria'])
                ->orderByDesc('fecha_inicio')
                ->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Cursos/Create', $this->opciones(null));
    }

    public function store(CursoRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['estado_curso'] = 'disponible';

        Curso::create($datos);

        return redirect()->route('cursos.index')->with('status', 'Curso creado correctamente.');
    }

    public function edit(Curso $curso): Response
    {
        return Inertia::render('Cursos/Edit', [
            'curso' => $curso,
            ...$this->opciones($curso),
        ]);
    }

    public function update(CursoRequest $request, Curso $curso): RedirectResponse
    {
        $curso->update($request->validated());

        return redirect()->route('cursos.index')->with('status', 'Curso actualizado correctamente.');
    }

    public function destroy(Curso $curso): RedirectResponse
    {
        try {
            $curso->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'No se puede eliminar: hay reservas o inscripciones asociadas a este curso.');
        }

        return redirect()->route('cursos.index')->with('status', 'Curso eliminado correctamente.');
    }

    /**
     * Instructor/vehículo/tipo de curso dados de baja quedan fuera de las opciones
     * para cursos nuevos, pero si $curso ya los tenía asignados (dado de baja después
     * de crearlo) se incluyen igual para no romper el formulario de edición.
     */
    private function opciones(?Curso $curso): array
    {
        return [
            'instructores' => Usuario::whereHas('rol', fn ($query) => $query->where('nombre', 'Instructor'))
                ->where(fn ($query) => $query->where('estado_usuario', 'activo')
                    ->when($curso, fn ($query) => $query->orWhere('id', $curso->instructor_id)))
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'apellido']),
            'vehiculos' => Vehiculo::where(fn ($query) => $query->where('estado_vehiculo', '!=', 'inactivo')
                    ->when($curso, fn ($query) => $query->orWhere('id', $curso->vehiculo_id)))
                ->orderBy('placa')
                ->get(['id', 'placa', 'marca', 'modelo']),
            'tiposCurso' => TipoCurso::where(fn ($query) => $query->where('estado_curso', 'activo')
                    ->when($curso, fn ($query) => $query->orWhere('id', $curso->tipo_curso_id)))
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'franjasHorarias' => FranjaHoraria::orderBy('hora_inicio')->get(['id', 'hora_inicio', 'hora_fin']),
        ];
    }
}
