<?php

namespace App\Jobs;

use App\Mail\PagoConfirmadoMail;
use App\Models\Pago;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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

        Mail::to($pago->correo_notificacion ?? $pago->inscripcion->estudiante->correo)
            ->send(new PagoConfirmadoMail($pago));

        $pago->update(['notificado' => true]);
    }
}
