<?php

namespace App\Http\Controllers;

use App\Http\Requests\InscripcionRequest;
use App\Models\Curso;
use App\Models\CuotaPlanPago;
use App\Models\Inscripcion;
use App\Models\PlanPago;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InscripcionController extends Controller
{
    public function index(): Response
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $query = Inscripcion::with(['curso.tipoCurso', 'estudiante', 'planPago']);

        if (! $usuario->hasRole(['Secretaria', 'Propietario'])) {
            $query->where('estudiante_id', $usuario->id);
        }

        return Inertia::render('Inscripciones/Index', [
            'inscripciones' => $query->orderByDesc('fecha_inscripcion')->paginate(10),
        ]);
    }

    public function create(Request $request): Response
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();
        $esPrivilegiado = $usuario->hasRole(['Secretaria', 'Propietario']);

        $reserva = null;

        if ($reservaId = $request->query('reserva_id')) {
            $reserva = Reserva::with('curso.tipoCurso')
                ->where('id', $reservaId)
                ->where('estado_reserva', 'pendiente')
                ->first();

            if ($reserva && ! $esPrivilegiado && $reserva->usuario_id !== $usuario->id) {
                abort(403);
            }
        }

        return Inertia::render('Inscripciones/Create', [
            'reserva' => $reserva,
            'cursos' => $reserva ? [] : Curso::where('estado_curso', 'disponible')->with(['tipoCurso', 'franjaHoraria'])->get(),
            'estudiantes' => $esPrivilegiado
                ? Usuario::whereHas('rol', fn ($q) => $q->where('nombre', 'Estudiante'))->orderBy('nombre')->get(['id', 'nombre', 'apellido'])
                : [],
            'planesPago' => PlanPago::where('estado', 'activo')->get(['id', 'nombre', 'numero_cuotas']),
        ]);
    }

    public function store(InscripcionRequest $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();
        $esPrivilegiado = $usuario->hasRole(['Secretaria', 'Propietario']);
        $reservaId = $request->validated('reserva_id');
        $planPago = PlanPago::findOrFail($request->validated('plan_pago_id'));

        try {
            DB::transaction(function () use ($request, $usuario, $esPrivilegiado, $reservaId, $planPago) {
                if ($reservaId) {
                    $reserva = Reserva::where('id', $reservaId)->where('estado_reserva', 'pendiente')->lockForUpdate()->firstOrFail();

                    if (! $esPrivilegiado && $reserva->usuario_id !== $usuario->id) {
                        abort(403);
                    }

                    $curso = Curso::where('id', $reserva->curso_id)->lockForUpdate()->firstOrFail();
                    $estudianteId = $reserva->usuario_id;
                } else {
                    $curso = Curso::where('id', $request->validated('curso_id'))
                        ->where('estado_curso', 'disponible')
                        ->lockForUpdate()
                        ->firstOrFail();
                    $estudianteId = $esPrivilegiado ? $request->validated('estudiante_id') : $usuario->id;
                }

                if ($this->tieneCursoEnCurso($estudianteId, $reservaId)) {
                    throw new \RuntimeException('curso_en_curso');
                }

                $curso->update(['estado_curso' => 'inscrito']);

                $inscripcion = Inscripcion::create([
                    'fecha_inscripcion' => now()->toDateString(),
                    'estado_inscripcion' => 'activa',
                    'monto_total' => $curso->precio_final,
                    'estudiante_id' => $estudianteId,
                    'plan_pago_id' => $planPago->id,
                    'curso_id' => $curso->id,
                ]);

                if ($reservaId) {
                    $reserva->update(['estado_reserva' => 'confirmada']);
                }

                $this->generarCuotas($inscripcion, $planPago);
            });
        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'El curso o la reserva ya no están disponibles.');
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Este estudiante ya tiene un curso reservado o en curso. Debe finalizarlo antes de inscribirse en otro.');
        }

        return redirect()->route('inscripciones.index')->with('status', 'Inscripción creada correctamente.');
    }

    private function tieneCursoEnCurso(int $estudianteId, ?int $reservaIdExcluida = null): bool
    {
        return Reserva::where('usuario_id', $estudianteId)
            ->where('estado_reserva', 'pendiente')
            ->when($reservaIdExcluida, fn ($query) => $query->where('id', '!=', $reservaIdExcluida))
            ->exists()
            || Inscripcion::where('estudiante_id', $estudianteId)->where('estado_inscripcion', 'activa')->exists();
    }

    private function generarCuotas(Inscripcion $inscripcion, PlanPago $planPago): void
    {
        $numeroCuotas = $planPago->numero_cuotas;
        $montoCuota = round($inscripcion->monto_total / $numeroCuotas, 2);
        $acumulado = 0;

        for ($i = 1; $i <= $numeroCuotas; $i++) {
            $monto = $i === $numeroCuotas ? round($inscripcion->monto_total - $acumulado, 2) : $montoCuota;
            $acumulado += $monto;

            CuotaPlanPago::create([
                'inscripcion_id' => $inscripcion->id,
                'nro_cuota' => $i,
                'monto' => $monto,
                'fecha_vencimiento' => now()->addMonths($i)->toDateString(),
                'estado_cuota' => 'pendiente',
            ]);
        }
    }
}
