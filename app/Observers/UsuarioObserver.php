<?php

namespace App\Observers;

use App\Models\Usuario;

class UsuarioObserver
{
    public function saved(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');
        $nombreRol = $usuario->rol?->nombre;

        if (! $nombreRol) {
            return;
        }

        $usuario->syncRoles([$nombreRol]);
    }
}
