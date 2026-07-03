<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Rol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RolController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Roles/Index', [
            'roles' => Rol::orderBy('nombre')->get(['id', 'nombre', 'descripcion'])
                ->map(fn (Rol $rol) => [
                    'id' => $rol->id,
                    'nombre' => $rol->nombre,
                    'descripcion' => $rol->descripcion,
                    'menu_ids' => $rol->menus()->pluck('menu.id'),
                ]),
            'menus' => Menu::orderBy('grupo')->orderBy('orden')->get(['id', 'nombre', 'grupo']),
        ]);
    }

    public function actualizarMenus(Request $request, Rol $rol): RedirectResponse
    {
        $validated = $request->validate([
            'menu_ids' => ['present', 'array'],
            'menu_ids.*' => ['integer', 'exists:menu,id'],
        ]);

        $rol->menus()->sync($validated['menu_ids']);

        return back()->with('status', 'Menús actualizados correctamente.');
    }
}
