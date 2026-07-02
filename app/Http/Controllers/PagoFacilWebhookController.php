<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Services\PagoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PagoFacilWebhookController extends Controller
{
    public function __construct(private PagoService $pagoService) {}

    /**
     * PagoFácil llama a esta URL (POST) cuando confirma/actualiza el estado de un pago.
     * Debe responder siempre HTTP 200, o PagoFácil reintentará indefinidamente.
     */
    public function callback(Request $request): JsonResponse
    {
        $pedidoId = (string) $request->input('PedidoID');
        $estadoNum = (int) $request->input('Estado');

        Log::info('[pagofacil.callback]', [
            'PedidoID' => $pedidoId,
            'Estado' => $estadoNum,
            'Fecha' => $request->input('Fecha'),
            'Hora' => $request->input('Hora'),
            'MetodoPago' => $request->input('MetodoPago'),
        ]);

        $pago = $this->pagoService->confirmarPago($pedidoId, $estadoNum);

        return response()->json([
            'error' => 0,
            'status' => 1,
            'message' => $pago ? 'Pago actualizado correctamente.' : 'Pago no encontrado o estado no reconocido.',
            'messageMostrar' => 0,
            'messageSistema' => '',
            'values' => (bool) $pago,
        ]);
    }

    /**
     * PagoFácil redirige aquí al cliente después del pago (o se usa como enlace de confirmación).
     */
    public function retorno(Request $request)
    {
        $pedidoId = $request->query('PedidoID');

        $pago = $pedidoId ? Pago::where('nro_pedido', $pedidoId)->first() : null;

        return Inertia::render('Pagofacil/Retorno', ['pago' => $pago]);
    }

    /**
     * PagoFácil puede llamar a esta URL para obtener la factura/recibo del pedido.
     */
    public function factura(Request $request): JsonResponse|Response
    {
        $pedidoId = (int) $request->input('PedidoID');

        if (! $pedidoId) {
            return response()->json(['error' => 1, 'status' => 0, 'message' => 'PedidoID inválido.', 'values' => false]);
        }

        $pago = Pago::where('nro_pedido', $pedidoId)->with('inscripcion.estudiante')->first();

        if (! $pago) {
            return response()->json(['error' => 1, 'status' => 0, 'message' => 'Pedido no encontrado.', 'values' => false]);
        }

        $pdf = Pdf::loadView('pdf.recibo-pago', ['pago' => $pago]);

        return new Response($pdf->output(), 200, ['Content-Type' => 'application/pdf']);
    }
}
