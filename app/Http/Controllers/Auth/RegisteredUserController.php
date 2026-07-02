<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\NotificarBienvenidaJob;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'nro_documento' => 'required|string|max:20|unique:'.Usuario::class.',nro_documento',
            'correo' => 'required|string|lowercase|email|max:150|unique:'.Usuario::class.',correo',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $rolEstudiante = Rol::where('nombre', 'Estudiante')->firstOrFail();

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'nro_documento' => $request->nro_documento,
            'correo' => $request->correo,
            'password' => Hash::make($request->password),
            'rol_id' => $rolEstudiante->id,
        ]);

        event(new Registered($usuario));

        NotificarBienvenidaJob::dispatch($usuario->id);

        Auth::login($usuario);

        return redirect(route('dashboard', absolute: false));
    }
}
