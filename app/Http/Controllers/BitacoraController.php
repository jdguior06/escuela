<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BitacoraController extends Controller
{
    public function index(Request $request): Response
    {
        $tipoEvento = $request->string('tipo_evento')->toString();

        $bitacora = Bitacora::with('usuario')
            ->when($tipoEvento, fn ($query, $tipoEvento) => $query->where('tipo_evento', $tipoEvento))
            ->orderByDesc('creado_en')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Bitacora/Index', [
            'bitacora' => $bitacora,
            'filters' => ['tipo_evento' => $tipoEvento],
        ]);
    }
}
