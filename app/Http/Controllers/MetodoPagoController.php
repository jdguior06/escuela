<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MetodoPagoController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $metodosPago = MetodoPago::when($search, fn ($query, $search) => $query->where('nombre', 'ilike', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('MetodosPago/Index', [
            'metodosPago' => $metodosPago,
            'filters' => ['search' => $search],
        ]);
    }
}
