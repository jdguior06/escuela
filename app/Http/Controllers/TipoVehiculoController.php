<?php

namespace App\Http\Controllers;

use App\Models\TipoVehiculo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TipoVehiculoController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $tiposVehiculo = TipoVehiculo::when($search, fn ($query, $search) => $query->where('nombre', 'ilike', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('TiposVehiculo/Index', [
            'tiposVehiculo' => $tiposVehiculo,
            'filters' => ['search' => $search],
        ]);
    }
}
