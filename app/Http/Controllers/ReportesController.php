<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\ControlCertificacion;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportesController extends Controller
{
    public function index(Request $request): Response
    {
        $fechaDesde = $request->filled('fecha_desde') ? Carbon::parse($request->string('fecha_desde')->toString())->startOfDay() : null;
        $fechaHasta = $request->filled('fecha_hasta') ? Carbon::parse($request->string('fecha_hasta')->toString())->endOfDay() : null;

        $cursosMasVendidos = $this->conRango(
            DB::table('inscripcion')
                ->join('curso', 'curso.id', '=', 'inscripcion.curso_id')
                ->join('tipo_curso', 'tipo_curso.id', '=', 'curso.tipo_curso_id'),
            'inscripcion.fecha_inscripcion',
            $fechaDesde,
            $fechaHasta,
        )
            ->select('tipo_curso.nombre', DB::raw('count(*) as total'))
            ->groupBy('tipo_curso.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $ingresosPorMes = $this->conRango(
            DB::table('pago')->where('estado_pago', 'pagado'),
            'fecha',
            $fechaDesde,
            $fechaHasta,
        )
            ->select(DB::raw("to_char(fecha, 'YYYY-MM') as mes"), DB::raw('sum(monto) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $certificacionesQuery = $this->conRango(ControlCertificacion::query()->toBase(), 'fecha_emision', $fechaDesde, $fechaHasta);
        $totalCertificaciones = (clone $certificacionesQuery)->count();
        $aprobados = (clone $certificacionesQuery)->where('estado_certificacion', 'aprobado')->count();
        $tasaAprobacion = $totalCertificaciones > 0 ? round($aprobados / $totalCertificaciones * 100, 1) : null;

        $recursosMasAccedidos = $this->conRango(
            DB::table('bitacora')->where('tipo_evento', 'acceso_recurso'),
            'creado_en',
            $fechaDesde,
            $fechaHasta,
        )
            ->select('recurso', DB::raw('count(*) as total'))
            ->groupBy('recurso')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $rolesMasActivos = $this->conRango(
            DB::table('bitacora')
                ->join('usuario', 'usuario.id', '=', 'bitacora.usuario_id')
                ->join('rol', 'rol.id', '=', 'usuario.rol_id')
                ->where('bitacora.tipo_evento', 'acceso_recurso'),
            'bitacora.creado_en',
            $fechaDesde,
            $fechaHasta,
        )
            ->select('rol.nombre', DB::raw('count(*) as total'))
            ->groupBy('rol.nombre')
            ->orderByDesc('total')
            ->get();

        $pagosPorMetodo = $this->conRango(
            DB::table('pago')
                ->join('metodo_pago', 'metodo_pago.id', '=', 'pago.metodo_id')
                ->where('pago.estado_pago', 'pagado'),
            'pago.fecha',
            $fechaDesde,
            $fechaHasta,
        )
            ->select('metodo_pago.nombre', DB::raw('count(*) as cantidad'), DB::raw('sum(pago.monto) as total'))
            ->groupBy('metodo_pago.nombre')
            ->orderByDesc('total')
            ->get();

        $cursosPorTipoVehiculo = $this->conRango(
            DB::table('inscripcion')
                ->join('curso', 'curso.id', '=', 'inscripcion.curso_id')
                ->join('tipo_curso', 'tipo_curso.id', '=', 'curso.tipo_curso_id')
                ->join('tipo_vehiculo', 'tipo_vehiculo.id', '=', 'tipo_curso.tipo_vehiculo_id'),
            'inscripcion.fecha_inscripcion',
            $fechaDesde,
            $fechaHasta,
        )
            ->select('tipo_vehiculo.nombre', DB::raw('count(*) as total'))
            ->groupBy('tipo_vehiculo.nombre')
            ->orderByDesc('total')
            ->get();

        return Inertia::render('Reportes/Index', [
            'cursosMasVendidos' => $cursosMasVendidos,
            'ingresosPorMes' => $ingresosPorMes,
            'certificaciones' => [
                'total' => $totalCertificaciones,
                'aprobados' => $aprobados,
                'tasaAprobacion' => $tasaAprobacion,
            ],
            'recursosMasAccedidos' => $recursosMasAccedidos,
            'rolesMasActivos' => $rolesMasActivos,
            'pagosPorMetodo' => $pagosPorMetodo,
            'cursosPorTipoVehiculo' => $cursosPorTipoVehiculo,
            'filters' => [
                'fecha_desde' => $fechaDesde?->toDateString(),
                'fecha_hasta' => $fechaHasta?->toDateString(),
            ],
        ]);
    }

    /** Aplica el rango de fechas elegido por el usuario a la columna indicada, si se especificó. */
    private function conRango(Builder $query, string $columna, ?Carbon $desde, ?Carbon $hasta): Builder
    {
        return $query
            ->when($desde, fn ($q) => $q->where($columna, '>=', $desde))
            ->when($hasta, fn ($q) => $q->where($columna, '<=', $hasta));
    }
}
