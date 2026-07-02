<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ContarVisitaPagina
{
    /** Rutas que no cuentan como "visita a una página" (polling, webhooks). */
    private const RUTAS_EXCLUIDAS = ['pagos.estado'];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('get') && $this->debeContar($request)) {
            DB::statement(
                'INSERT INTO visita_pagina (pagina, contador, actualizado_en) VALUES (?, 1, now())
                 ON CONFLICT (pagina) DO UPDATE SET contador = visita_pagina.contador + 1, actualizado_en = now()',
                [$request->path()]
            );
        }

        return $next($request);
    }

    private function debeContar(Request $request): bool
    {
        $nombreRuta = $request->route()?->getName();

        if (! $nombreRuta) {
            return false;
        }

        return ! in_array($nombreRuta, self::RUTAS_EXCLUIDAS, true) && ! str_starts_with($nombreRuta, 'pagofacil.');
    }
}
