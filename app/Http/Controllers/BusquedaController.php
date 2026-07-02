<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Usuario;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BusquedaController extends Controller
{
    public function index(Request $request): Response
    {
        $q = $request->string('q')->toString();

        /** @var Usuario $usuario */
        $usuario = Auth::user();
        $usuario->loadMissing('rol.menus');
        $rutasVisibles = $usuario->rol?->menus->pluck('ruta') ?? collect();

        $resultados = [
            'cursos' => [],
            'instructores' => [],
            'vehiculos' => [],
        ];

        if ($q !== '') {
            if ($rutasVisibles->contains('/cursos') || $usuario->hasRole('Estudiante')) {
                $resultados['cursos'] = Curso::with('tipoCurso')
                    ->whereHas('tipoCurso', fn ($query) => $query->where('nombre', 'ilike', "%{$q}%"))
                    ->limit(10)
                    ->get();
            }

            if ($rutasVisibles->contains('/usuarios')) {
                $resultados['instructores'] = Usuario::whereHas('rol', fn ($query) => $query->where('nombre', 'Instructor'))
                    ->where(fn ($query) => $query->where('nombre', 'ilike', "%{$q}%")->orWhere('apellido', 'ilike', "%{$q}%"))
                    ->limit(10)
                    ->get(['id', 'nombre', 'apellido', 'correo']);
            }

            if ($rutasVisibles->contains('/vehiculos')) {
                $resultados['vehiculos'] = Vehiculo::where('placa', 'ilike', "%{$q}%")
                    ->orWhere('marca', 'ilike', "%{$q}%")
                    ->orWhere('modelo', 'ilike', "%{$q}%")
                    ->limit(10)
                    ->get();
            }
        }

        return Inertia::render('Buscar/Index', [
            'q' => $q,
            'resultados' => $resultados,
        ]);
    }
}
