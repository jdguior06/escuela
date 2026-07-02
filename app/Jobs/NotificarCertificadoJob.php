<?php

namespace App\Jobs;

use App\Mail\CertificadoEmitidoMail;
use App\Models\ControlCertificacion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificarCertificadoJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private int $certificacionId) {}

    public function handle(): void
    {
        $certificacion = ControlCertificacion::with('inscripcion.estudiante')->findOrFail($this->certificacionId);

        try {
            Mail::to($certificacion->inscripcion->estudiante->correo)
                ->send(new CertificadoEmitidoMail($certificacion));
        } catch (\Throwable $e) {
            // No se relanza: con QUEUE_CONNECTION=sync esto corre dentro de la
            // misma petición que emite el certificado, y un fallo de correo no
            // debe deshacer el certificado ya registrado.
            Log::error('[certificado] no se pudo enviar el correo', [
                'certificacion_id' => $this->certificacionId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
