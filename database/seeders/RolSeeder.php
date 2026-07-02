<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Propietario' => 'Administrador del Negocio',
            'Instructor' => 'Profesor de conducción',
            'Estudiante' => 'Alumno de conducción',
            'Secretaria' => 'Encargada de atención al cliente y administración',
        ];

        foreach ($roles as $nombre => $descripcion) {
            Rol::updateOrCreate(['nombre' => $nombre], ['descripcion' => $descripcion]);
            Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }
    }
}
