<?php

namespace App\Jobs;

use App\Mail\BienvenidaMail;
use App\Models\Usuario;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class NotificarBienvenidaJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private int $usuarioId) {}

    public function handle(): void
    {
        $usuario = Usuario::findOrFail($this->usuarioId);

        Mail::to($usuario->correo)->send(new BienvenidaMail($usuario));
    }
}
