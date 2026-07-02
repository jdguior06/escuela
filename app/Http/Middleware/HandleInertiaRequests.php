<?php

namespace App\Http\Middleware;

use App\Models\VisitaPagina;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $user?->loadMissing('rol');

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'rol' => $user?->rol?->nombre,
            ],
            'menu' => $user?->rol
                ? $user->rol->menus()->where('activo', true)->orderBy('orden')->get(['menu.id', 'nombre', 'ruta', 'icono', 'grupo', 'padre_id'])
                : [],
            'flash' => [
                'status' => $request->session()->get('status'),
                'error' => $request->session()->get('error'),
                'qr' => $request->session()->get('qr'),
            ],
            'visitas' => VisitaPagina::where('pagina', $request->path())->value('contador') ?? 0,
        ];
    }
}
