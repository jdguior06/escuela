<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $propietario = Rol::where('nombre', 'Propietario')->firstOrFail();
        $instructor = Rol::where('nombre', 'Instructor')->firstOrFail();

        Usuario::firstOrCreate(
            ['correo' => 'j.d.guior010602@gmail.com'],
            [
                'nombre' => 'Admin',
                'apellido' => 'Sistema',
                'nro_documento' => '0000001',
                'password' => Hash::make('password'),
                'estado_usuario' => 'activo',
                'rol_id' => $propietario->id,
            ]
        );

        $instructores = [
            ['correo' => 'instructor@grupo13sa.com', 'nombre' => 'Carlos', 'apellido' => 'Rojas', 'nro_documento' => '0000002', 'genero' => 'M'],
            ['correo' => 'maria.fernandez@grupo13sa.com', 'nombre' => 'María', 'apellido' => 'Fernández', 'nro_documento' => '0000003', 'genero' => 'F'],
            ['correo' => 'jorge.quispe@grupo13sa.com', 'nombre' => 'Jorge', 'apellido' => 'Quispe', 'nro_documento' => '0000004', 'genero' => 'M'],
            ['correo' => 'luis.mamani@grupo13sa.com', 'nombre' => 'Luis', 'apellido' => 'Mamani', 'nro_documento' => '0000005', 'genero' => 'M'],
            ['correo' => 'ana.vargas@grupo13sa.com', 'nombre' => 'Ana', 'apellido' => 'Vargas', 'nro_documento' => '0000006', 'genero' => 'F'],
        ];

        foreach ($instructores as $datos) {
            Usuario::firstOrCreate(
                ['correo' => $datos['correo']],
                [
                    'nombre' => $datos['nombre'],
                    'apellido' => $datos['apellido'],
                    'nro_documento' => $datos['nro_documento'],
                    'genero' => $datos['genero'],
                    'password' => Hash::make('password'),
                    'estado_usuario' => 'activo',
                    'rol_id' => $instructor->id,
                ]
            );
        }
    }
}
