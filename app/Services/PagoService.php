<?php

namespace App\Services;

use App\Jobs\NotificarPagoConfirmadoJob;
use App\Models\CuotaPlanPago;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Usuario;
use App\Services\PagoFacil\PagoFacilClient;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PagoService
{
    public function __construct(private PagoFacilClient $pagoFacil) {}

    public function registrarPago(Inscripcion $inscripcion, int $metodoId, Usuario $actor): array
    {
        $metodoPago = MetodoPago::whereIn('nombre', ['Efectivo', 'QR'])->find($metodoId);

        if (! $metodoPago) {
            throw new InvalidArgumentException('Método de pago inválido: use Efectivo o QR.');
        }

        // El pago en efectivo es presencial: solo Secretaria/Propietario lo registran
        // en el mostrador. El Estudiante paga en línea, así que solo tiene QR.
        if ($metodoPago->nombre === 'Efectivo' && ! $actor->hasRole(['Secretaria', 'Propietario'])) {
            throw new InvalidArgumentException('El pago en efectivo debe registrarlo Secretaria o Propietario de forma presencial. Para pagar en línea, use QR.');
        }

        $inscripcion->loadMissing('planPago');
        $totalCuotas = $inscripcion->planPago->numero_cuotas;
        $cuotasPagadas = Pago::where('inscripcion_id', $inscripcion->id)->where('estado_pago', 'pagado')->count();

        if ($cuotasPagadas >= $totalCuotas) {
            throw new DomainException("La inscripción ya tiene todas sus cuotas pagadas ({$totalCuotas}/{$totalCuotas}).");
        }

        $nroCuota = $cuotasPagadas + 1;

        $cuota = CuotaPlanPago::where('inscripcion_id', $inscripcion->id)
            ->where('nro_cuota', $nroCuota)
            ->firstOrFail();

        return $metodoPago->nombre === 'Efectivo'
            ? $this->pagarEfectivo($inscripcion, $metodoPago, $cuota)
            : $this->pagarConQr($inscripcion, $metodoPago, $cuota, $actor->correo);
    }

    private function pagarEfectivo(Inscripcion $inscripcion, MetodoPago $metodoPago, CuotaPlanPago $cuota): array
    {
        $pago = DB::transaction(function () use ($inscripcion, $metodoPago, $cuota) {
            $pago = Pago::create([
                'fecha' => now()->toDateString(),
                'monto' => $cuota->monto,
                'nro_cuota' => $cuota->nro_cuota,
                'estado_pago' => 'pagado',
                'notificado' => true,
                'usuario_id' => $inscripcion->estudiante_id,
                'metodo_id' => $metodoPago->id,
                'inscripcion_id' => $inscripcion->id,
            ]);

            $cuota->update(['estado_cuota' => 'pagada']);

            return $pago;
        });

        return ['tipo' => 'efectivo', 'pago' => $pago];
    }

    private function pagarConQr(Inscripcion $inscripcion, MetodoPago $metodoPago, CuotaPlanPago $cuota, string $correoSender): array
    {
        $inscripcion->loadMissing('estudiante', 'curso.tipoCurso');

        $pago = Pago::create([
            'fecha' => now()->toDateString(),
            'monto' => $cuota->monto,
            'nro_cuota' => $cuota->nro_cuota,
            'estado_pago' => 'pendiente',
            'correo_notificacion' => $correoSender,
            'usuario_id' => $inscripcion->estudiante_id,
            'metodo_id' => $metodoPago->id,
            'inscripcion_id' => $inscripcion->id,
        ]);

        $paymentNumber = now()->format('YmdHis').str_pad((string) $pago->id, 4, '0', STR_PAD_LEFT);

        // El monto guardado en `pago.monto` es siempre el monto real de la cuota;
        // solo el monto enviado a la API externa se ajusta por el divisor de sandbox
        // (1000 mientras se prueba, 1 en producción — ver config/services.php).
        $montoApi = round($cuota->monto / config('services.pagofacil.sandbox_divisor'), 4);

        try {
            $qr = $this->pagoFacil->generarQr([
                'paymentMethod' => 34,
                'clientName' => trim($inscripcion->estudiante->nombre.' '.$inscripcion->estudiante->apellido),
                'documentType' => 1,
                'documentId' => $inscripcion->estudiante->nro_documento,
                'phoneNumber' => config('services.pagofacil.notification_phone'),
                'email' => $correoSender,
                'paymentNumber' => $paymentNumber,
                'amount' => $montoApi,
                'currency' => 2,
                'clientCode' => config('services.pagofacil.client_code'),
                'callbackUrl' => config('services.pagofacil.callback_url'),
                'orderDetail' => [[
                    'serial' => 1,
                    'product' => $inscripcion->curso->tipoCurso->nombre,
                    'quantity' => 1,
                    'price' => $montoApi,
                    'discount' => 0,
                    'total' => $montoApi,
                ]],
            ]);
        } catch (\Throwable $e) {
            Log::channel('pagos')->error('[pago.qr] generarQr lanzó excepción', [
                'pago_id' => $pago->id,
                'inscripcion_id' => $inscripcion->id,
                'error' => $e->getMessage(),
            ]);
            $pago->delete();
            throw $e;
        }

        $pago->update([
            'id_transaccion' => $qr['transaction_id'],
            'nro_pedido' => $paymentNumber,
        ]);

        return [
            'tipo' => 'qr',
            'pago' => $pago,
            'qr_base64' => $qr['qr_base64'],
            'transaction_id' => $qr['transaction_id'],
        ];
    }

    /** Llamado desde el callback público de PagoFácil (PedidoID + Estado). */
    public function confirmarPago(string $pedidoId, int $estadoNum): ?Pago
    {
        $estadoTxt = Pago::ESTADOS[$estadoNum] ?? null;

        if (! $estadoTxt) {
            Log::channel('pagos')->warning('[pago.confirmar] estado no reconocido', [
                'pedido_id' => $pedidoId,
                'estado_num' => $estadoNum,
            ]);

            return null;
        }

        return DB::transaction(function () use ($pedidoId, $estadoTxt) {
            $pago = Pago::where('nro_pedido', $pedidoId)->lockForUpdate()->first();

            if (! $pago) {
                Log::channel('pagos')->warning('[pago.confirmar] pedido no encontrado', ['pedido_id' => $pedidoId]);

                return null;
            }

            $pago->update(['estado_pago' => $estadoTxt]);

            Log::channel('pagos')->info('[pago.confirmar] estado actualizado', [
                'pago_id' => $pago->id,
                'pedido_id' => $pedidoId,
                'estado' => $estadoTxt,
            ]);

            if ($estadoTxt === 'pagado') {
                CuotaPlanPago::where('inscripcion_id', $pago->inscripcion_id)
                    ->where('nro_cuota', $pago->nro_cuota)
                    ->update(['estado_cuota' => 'pagada']);

                if (! $pago->notificado) {
                    NotificarPagoConfirmadoJob::dispatch($pago->id)->afterCommit();
                }
            }

            return $pago;
        });
    }
}
