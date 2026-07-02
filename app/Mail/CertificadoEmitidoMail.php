<?php

namespace App\Mail;

use App\Models\ControlCertificacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CertificadoEmitidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ControlCertificacion $certificacion) {}

    public function build()
    {
        return $this->subject('Tu certificado está listo')
            ->view('emails.certificado-emitido', ['certificacion' => $this->certificacion])
            ->attach(Storage::disk('local')->path($this->certificacion->pdf_path), [
                'as' => 'certificado.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
