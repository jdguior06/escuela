<?php

namespace App\Http\Controllers;

use App\Http\Requests\ControlCertificacionRequest;
use App\Models\ControlCertificacion;
use App\Models\Inscripcion;
use App\Models\Usuario;
use App\Services\CertificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ControlCertificacionController extends Controller
{
    public function __construct(private CertificacionService $certificacionService) {}

    public function index(): InertiaResponse
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();
        $esInstructor = $usuario->hasRole('Instructor');
        $esEstudiante = $usuario->hasRole('Estudiante');

        $pendientes = $esEstudiante
            ? collect()
            : Inscripcion::with(['estudiante', 'curso.tipoCurso'])
                ->where('estado_inscripcion', 'activa')
                ->whereHas('curso', fn ($query) => $query->where('estado_curso', 'inscrito'))
                ->doesntHave('controlCertificacion')
                ->when($esInstructor, fn ($query) => $query->whereHas('curso', fn ($q) => $q->where('instructor_id', $usuario->id)))
                ->get();

        $emitidas = ControlCertificacion::with(['inscripcion.estudiante', 'inscripcion.curso.tipoCurso'])
            ->when($esInstructor, fn ($query) => $query->whereHas('inscripcion.curso', fn ($q) => $q->where('instructor_id', $usuario->id)))
            ->when($esEstudiante, fn ($query) => $query->whereHas('inscripcion', fn ($q) => $q->where('estudiante_id', $usuario->id)))
            ->orderByDesc('fecha_emision')
            ->get();

        return Inertia::render('ControlCertificacion/Index', [
            'pendientes' => $pendientes,
            'emitidas' => $emitidas,
        ]);
    }

    public function create(Inscripcion $inscripcion): InertiaResponse
    {
        $this->autorizarGestion($inscripcion);

        return Inertia::render('ControlCertificacion/Create', [
            'inscripcion' => $inscripcion->load('estudiante', 'curso.tipoCurso', 'curso.instructor'),
        ]);
    }

    public function store(ControlCertificacionRequest $request): RedirectResponse
    {
        $inscripcion = Inscripcion::with('curso')->findOrFail($request->validated('inscripcion_id'));
        $this->autorizarGestion($inscripcion);

        $certificacion = $this->certificacionService->registrar($inscripcion, (float) $request->validated('nota'));

        $mensaje = $certificacion->estado_certificacion === 'aprobado'
            ? 'Certificación registrada: aprobado. El estudiante ya puede ver y descargar su certificado.'
            : 'Certificación registrada: reprobado.';

        return redirect()->route('control-certificacion.index')->with('status', $mensaje);
    }

    public function descargar(ControlCertificacion $controlCertificacion): BinaryFileResponse
    {
        $controlCertificacion->loadMissing('inscripcion.estudiante', 'inscripcion.curso');

        /** @var Usuario $usuario */
        $usuario = Auth::user();
        $curso = $controlCertificacion->inscripcion->curso;

        $autorizado = $usuario->hasRole('Propietario')
            || ($usuario->hasRole('Instructor') && $curso->instructor_id === $usuario->id)
            || $controlCertificacion->inscripcion->estudiante_id === $usuario->id;

        if (! $autorizado) {
            abort(403);
        }

        if ($controlCertificacion->estado_certificacion !== 'aprobado' || ! $controlCertificacion->pdf_path) {
            abort(404);
        }

        return response()->file(Storage::disk('local')->path($controlCertificacion->pdf_path));
    }

    private function autorizarGestion(Inscripcion $inscripcion): void
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();
        $inscripcion->loadMissing('curso');

        $autorizado = $usuario->hasRole('Propietario')
            || ($usuario->hasRole('Instructor') && $inscripcion->curso->instructor_id === $usuario->id);

        if (! $autorizado) {
            abort(403);
        }
    }
}
