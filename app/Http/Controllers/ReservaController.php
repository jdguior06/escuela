<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservaRequest;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReservaController extends Controller
{
    public function index(): Response
    {
        Reserva::where('estado_reserva', 'pendiente')
            ->where('fecha_vencimiento', '<', now())
            ->each(function (Reserva $reserva) {
                DB::transaction(function () use ($reserva) {
                    $reserva->update(['estado_reserva' => 'vencida']);
                    $reserva->curso->update(['estado_curso' => 'disponible', 'reservado_por' => null]);
                });
            });

        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $query = Reserva::with(['curso.tipoCurso', 'usuario']);

        if (! $usuario->hasRole(['Secretaria', 'Propietario'])) {
            $query->where('usuario_id', $usuario->id);
        }

        return Inertia::render('Reservas/Index', [
            'reservas' => $query->orderByDesc('fecha_reserva')->paginate(10),
        ]);
    }

    public function create(): Response
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();
        $esPrivilegiado = $usuario->hasRole(['Secretaria', 'Propietario']);

        return Inertia::render('Reservas/Create', [
            'cursos' => Curso::where('estado_curso', 'disponible')->with(['tipoCurso', 'franjaHoraria'])->get(),
            'estudiantes' => $esPrivilegiado
                ? Usuario::whereHas('rol', fn ($q) => $q->where('nombre', 'Estudiante'))->orderBy('nombre')->get(['id', 'nombre', 'apellido'])
                : [],
        ]);
    }

    public function store(ReservaRequest $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();
        $usuarioId = $usuario->hasRole(['Secretaria', 'Propietario'])
            ? $request->validated('usuario_id')
            : $usuario->id;

        if ($this->tieneCursoEnCurso($usuarioId)) {
            return back()->with('error', 'Este estudiante ya tiene un curso reservado o en curso. Debe finalizarlo antes de reservar otro.');
        }

        try {
            DB::transaction(function () use ($request, $usuarioId) {
                $curso = Curso::where('id', $request->validated('curso_id'))
                    ->where('estado_curso', 'disponible')
                    ->lockForUpdate()
                    ->firstOrFail();

                $curso->update(['estado_curso' => 'reservado', 'reservado_por' => $usuarioId]);

                Reserva::create([
                    'usuario_id' => $usuarioId,
                    'curso_id' => $curso->id,
                    'fecha_vencimiento' => now()->addDays(3),
                    'estado_reserva' => 'pendiente',
                ]);
            });
        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Este curso ya no está disponible.');
        }

        return redirect()->route('reservas.index')->with('status', 'Reserva creada correctamente.');
    }

    private function tieneCursoEnCurso(int $usuarioId): bool
    {
        return Reserva::where('usuario_id', $usuarioId)->where('estado_reserva', 'pendiente')->exists()
            || Inscripcion::where('estudiante_id', $usuarioId)->where('estado_inscripcion', 'activa')->exists();
    }

    public function destroy(Reserva $reserva): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        if (! $usuario->hasRole(['Secretaria', 'Propietario']) && $reserva->usuario_id !== $usuario->id) {
            abort(403);
        }

        if ($reserva->estado_reserva !== 'pendiente') {
            return back()->with('error', 'Solo se pueden cancelar reservas pendientes.');
        }

        DB::transaction(function () use ($reserva) {
            $reserva->update(['estado_reserva' => 'cancelada']);
            $reserva->curso->update(['estado_curso' => 'disponible', 'reservado_por' => null]);
        });

        return redirect()->route('reservas.index')->with('status', 'Reserva cancelada correctamente.');
    }
}
