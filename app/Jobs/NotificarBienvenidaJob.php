<?php

namespace App\Jobs;

use App\Mail\BienvenidaMail;
use App\Models\Usuario;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificarBienvenidaJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private int $usuarioId) {}

    public function handle(): void
    {
        $usuario = Usuario::findOrFail($this->usuarioId);

        try {
            Mail::to($usuario->correo)->send(new BienvenidaMail($usuario));
        } catch (\Throwable $e) {
            // No se relanza: con QUEUE_CONNECTION=sync esto corre dentro de la
            // misma petición de registro/creación, y un fallo de correo no debe
            // impedir que la cuenta quede creada.
            Log::error('[bienvenida] no se pudo enviar el correo', [
                'usuario_id' => $this->usuarioId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
