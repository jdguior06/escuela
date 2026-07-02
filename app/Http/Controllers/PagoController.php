<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagoRequest;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Usuario;
use App\Services\PagoService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class PagoController extends Controller
{
    public function __construct(private PagoService $pagoService) {}

    public function listado(Request $request): Response
    {
        $estadoPago = $request->string('estado_pago')->toString();

        $pagos = Pago::with(['metodoPago', 'inscripcion.estudiante', 'inscripcion.curso.tipoCurso'])
            ->when($estadoPago, fn ($query, $estadoPago) => $query->where('estado_pago', $estadoPago))
            ->orderByDesc('fecha')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Pagos/Listado', [
            'pagos' => $pagos,
            'filters' => ['estado_pago' => $estadoPago],
        ]);
    }

    public function index(Inscripcion $inscripcion): Response
    {
        $this->autorizar($inscripcion);

        /** @var Usuario $usuario */
        $usuario = Auth::user();

        // El Estudiante paga en línea: solo ve QR. Efectivo es exclusivo del
        // registro presencial que hace Secretaria/Propietario (ver PagoService).
        $nombresMetodos = $usuario->hasRole(['Secretaria', 'Propietario']) ? ['Efectivo', 'QR'] : ['QR'];

        return Inertia::render('Pagos/Index', [
            'inscripcion' => $inscripcion->load('planPago', 'curso.tipoCurso'),
            'cuotas' => $inscripcion->cuotasPlanPago()->orderBy('nro_cuota')->get(),
            'pagos' => $inscripcion->pagos()->with('metodoPago')->orderBy('nro_cuota')->get(),
            'metodosPago' => MetodoPago::whereIn('nombre', $nombresMetodos)->get(['id', 'nombre']),
        ]);
    }

    public function store(StorePagoRequest $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $inscripcion = Inscripcion::findOrFail($request->validated('inscripcion_id'));
        $this->autorizar($inscripcion);

        try {
            $resultado = $this->pagoService->registrarPago(
                $inscripcion,
                $request->validated('metodo_id'),
                $usuario,
            );
        } catch (InvalidArgumentException|DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo generar el pago con PagoFácil: '.$e->getMessage());
        }

        if ($resultado['tipo'] === 'qr') {
            return back()->with('qr', [
                'base64' => $resultado['qr_base64'],
                'transaction_id' => $resultado['transaction_id'],
                'pago_id' => $resultado['pago']->id,
            ]);
        }

        return back()->with('status', 'Pago en efectivo registrado correctamente.');
    }

    public function estado(Pago $pago): JsonResponse
    {
        $this->autorizar($pago->inscripcion);

        return response()->json(['estado_pago' => $pago->estado_pago]);
    }

    private function autorizar(Inscripcion $inscripcion): void
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        if (! $usuario->hasRole(['Secretaria', 'Propietario']) && $inscripcion->estudiante_id !== $usuario->id) {
            abort(403);
        }
    }
}
