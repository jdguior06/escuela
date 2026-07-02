<?php

namespace App\Http\Middleware;

use App\Models\Bitacora;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RegistrarAccesoRecurso
{
    /** Rutas que no se registran como "acceso a un recurso" (polling, webhooks). */
    private const RUTAS_EXCLUIDAS = ['pagos.estado'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check() && $request->isMethod('get') && $this->debeRegistrar($request)) {
            Bitacora::create([
                'usuario_id' => Auth::id(),
                'tipo_evento' => 'acceso_recurso',
                'recurso' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }

    private function debeRegistrar(Request $request): bool
    {
        $nombreRuta = $request->route()?->getName();

        if (! $nombreRuta) {
            return false;
        }

        return ! in_array($nombreRuta, self::RUTAS_EXCLUIDAS, true) && ! str_starts_with($nombreRuta, 'pagofacil.');
    }
}
