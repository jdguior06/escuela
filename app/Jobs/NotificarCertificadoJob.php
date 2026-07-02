<?php

namespace App\Jobs;

use App\Mail\CertificadoEmitidoMail;
use App\Models\ControlCertificacion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class NotificarCertificadoJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private int $certificacionId) {}

    public function handle(): void
    {
        $certificacion = ControlCertificacion::with('inscripcion.estudiante')->findOrFail($this->certificacionId);

        Mail::to($certificacion->inscripcion->estudiante->correo)
            ->send(new CertificadoEmitidoMail($certificacion));
    }
}
