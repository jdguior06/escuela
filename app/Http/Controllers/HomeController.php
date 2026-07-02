<?php

namespace App\Http\Controllers;

use App\Models\FranjaHoraria;
use App\Models\TipoCurso;
use App\Models\TipoVehiculo;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'tiposCurso' => TipoCurso::where('estado_curso', 'activo')->with('tipoVehiculo')->orderBy('nombre')->get(),
            'tiposVehiculo' => TipoVehiculo::orderBy('nombre')->get(),
            'franjasHorarias' => FranjaHoraria::orderBy('hora_inicio')->get(),
        ]);
    }
}
