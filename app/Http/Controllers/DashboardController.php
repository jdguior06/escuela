<?php

namespace App\Http\Controllers;

use App\Models\ControlCertificacion;
use App\Models\Curso;
use App\Models\CuotaPlanPago;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();
        $usuario->loadMissing('rol');
        $rol = $usuario->rol?->nombre;

        return Inertia::render('Dashboard', [
            'rol' => $rol,
            'datos' => match ($rol) {
                'Propietario' => $this->datosPropietario(),
                'Secretaria' => $this->datosSecretaria(),
                'Instructor' => $this->datosInstructor($usuario),
                'Estudiante' => $this->datosEstudiante($usuario),
                default => [],
            },
        ]);
    }

    private function datosPropietario(): array
    {
        return [
            'usuariosActivos' => Usuario::where('estado_usuario', 'activo')->count(),
            'cursosActivos' => Curso::whereIn('estado_curso', ['disponible', 'reservado', 'inscrito'])->count(),
            'inscripcionesMes' => Inscripcion::whereMonth('fecha_inscripcion', now()->month)
                ->whereYear('fecha_inscripcion', now()->year)
                ->count(),
            'ingresosMes' => (float) Pago::where('estado_pago', 'pagado')
                ->whereMonth('fecha', now()->month)
                ->whereYear('fecha', now()->year)
                ->sum('monto'),
            'ingresosUltimosMeses' => $this->ingresosUltimosMeses(),
            'cursosPorEstado' => $this->cursosPorEstado(),
        ];
    }

    private function datosSecretaria(): array
    {
        return [
            'reservasPendientes' => Reserva::where('estado_reserva', 'pendiente')->count(),
            'inscripcionesHoy' => Inscripcion::whereDate('fecha_inscripcion', now()->toDateString())->count(),
            'cursosDisponibles' => Curso::where('estado_curso', 'disponible')->count(),
            'pagosPendientes' => Pago::where('estado_pago', 'pendiente')->count(),
            'cursosPorEstado' => $this->cursosPorEstado(),
        ];
    }

    /** Últimos 6 meses (incluido el actual), con 0 en los meses sin pagos confirmados. */
    private function ingresosUltimosMeses(): array
    {
        $desde = now()->startOfMonth()->subMonths(5);

        $porMes = Pago::where('estado_pago', 'pagado')
            ->where('fecha', '>=', $desde->toDateString())
            ->select(DB::raw("to_char(fecha, 'YYYY-MM') as mes"), DB::raw('sum(monto) as total'))
            ->groupBy('mes')
            ->pluck('total', 'mes');

        return collect(range(0, 5))
            ->map(function ($i) use ($desde, $porMes) {
                $mes = $desde->copy()->addMonths($i)->format('Y-m');

                return ['mes' => $mes, 'total' => (float) ($porMes[$mes] ?? 0)];
            })
            ->values()
            ->all();
    }

    private function cursosPorEstado(): array
    {
        return Curso::select('estado_curso', DB::raw('count(*) as total'))
            ->groupBy('estado_curso')
            ->pluck('total', 'estado_curso')
            ->all();
    }

    private function datosInstructor(Usuario $usuario): array
    {
        $proximaClase = Curso::where('instructor_id', $usuario->id)
            ->whereIn('estado_curso', ['reservado', 'inscrito'])
            ->with(['tipoCurso', 'franjaHoraria'])
            ->orderBy('fecha_inicio')
            ->first();

        return [
            'cursosAsignados' => Curso::where('instructor_id', $usuario->id)
                ->whereIn('estado_curso', ['reservado', 'inscrito'])
                ->count(),
            'certificacionesPendientes' => Inscripcion::where('estado_inscripcion', 'activa')
                ->whereHas('curso', fn ($q) => $q->where('estado_curso', 'inscrito')->where('instructor_id', $usuario->id))
                ->doesntHave('controlCertificacion')
                ->count(),
            'proximaClase' => $proximaClase,
            'certificacionesPorEstado' => ControlCertificacion::whereHas(
                'inscripcion.curso',
                fn ($q) => $q->where('instructor_id', $usuario->id)
            )
                ->select('estado_certificacion', DB::raw('count(*) as total'))
                ->groupBy('estado_certificacion')
                ->pluck('total', 'estado_certificacion')
                ->all(),
        ];
    }

    private function datosEstudiante(Usuario $usuario): array
    {
        $proximaInscripcion = Inscripcion::where('estudiante_id', $usuario->id)
            ->where('estado_inscripcion', 'activa')
            ->with(['curso.tipoCurso', 'curso.franjaHoraria'])
            ->orderByDesc('fecha_inscripcion')
            ->first();

        return [
            'inscripcionesActivas' => Inscripcion::where('estudiante_id', $usuario->id)
                ->where('estado_inscripcion', 'activa')
                ->count(),
            'proximaInscripcion' => $proximaInscripcion,
            'cuotasPendientes' => CuotaPlanPago::whereHas(
                'inscripcion',
                fn ($q) => $q->where('estudiante_id', $usuario->id)
            )->where('estado_cuota', 'pendiente')->count(),
            'cuotasPorEstado' => CuotaPlanPago::whereHas(
                'inscripcion',
                fn ($q) => $q->where('estudiante_id', $usuario->id)
            )
                ->select('estado_cuota', DB::raw('count(*) as total'))
                ->groupBy('estado_cuota')
                ->pluck('total', 'estado_cuota')
                ->all(),
            'certificacionAprobada' => Inscripcion::where('estudiante_id', $usuario->id)
                ->whereHas('controlCertificacion', fn ($q) => $q->where('estado_certificacion', 'aprobado'))
                ->exists(),
        ];
    }
}
