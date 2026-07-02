<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BienvenidaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Usuario $usuario) {}

    public function build()
    {
        return $this->subject('Bienvenido a '.config('app.name'))
            ->view('emails.bienvenida', ['usuario' => $this->usuario]);
    }
}
