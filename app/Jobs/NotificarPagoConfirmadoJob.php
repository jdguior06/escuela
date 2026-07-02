<?php

namespace App\Jobs;

use App\Mail\PagoConfirmadoMail;
use App\Models\Pago;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificarPagoConfirmadoJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private int $pagoId) {}

    public function handle(): void
    {
        $pago = Pago::with('inscripcion.estudiante')->findOrFail($this->pagoId);

        if ($pago->notificado) {
            return;
        }

        try {
            Mail::to($pago->correo_notificacion ?? $pago->inscripcion->estudiante->correo)
                ->send(new PagoConfirmadoMail($pago));

            $pago->update(['notificado' => true]);
        } catch (\Throwable $e) {
            // No se relanza: con QUEUE_CONNECTION=sync esto corre dentro de la
            // misma petición que confirma el pago, y un fallo de correo no debe
            // deshacer la confirmación del pago ya registrada.
            Log::channel('pagos')->error('[pago.notificar] no se pudo enviar el correo', [
                'pago_id' => $this->pagoId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
