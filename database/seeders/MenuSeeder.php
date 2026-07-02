<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nombre' => 'Dashboard', 'ruta' => '/dashboard', 'icono' => 'home', 'grupo' => null, 'orden' => 1, 'roles' => ['Propietario', 'Secretaria', 'Instructor', 'Estudiante']],

            ['nombre' => 'Reservas', 'ruta' => '/reservas', 'icono' => 'calendar', 'grupo' => 'Operación', 'orden' => 2, 'roles' => ['Propietario', 'Secretaria', 'Estudiante']],
            ['nombre' => 'Cursos', 'ruta' => '/cursos', 'icono' => 'book', 'grupo' => 'Operación', 'orden' => 3, 'roles' => ['Propietario', 'Secretaria', 'Instructor']],
            ['nombre' => 'Inscripciones', 'ruta' => '/inscripciones', 'icono' => 'clipboard', 'grupo' => 'Operación', 'orden' => 4, 'roles' => ['Propietario', 'Secretaria', 'Estudiante']],
            ['nombre' => 'Certificados', 'ruta' => '/control-certificacion', 'icono' => 'award', 'grupo' => 'Operación', 'orden' => 5, 'roles' => ['Instructor', 'Propietario', 'Estudiante']],
            ['nombre' => 'Pagos', 'ruta' => '/pagos', 'icono' => 'credit-card', 'grupo' => 'Operación', 'orden' => 6, 'roles' => ['Secretaria', 'Propietario']],

            ['nombre' => 'Métodos de pago', 'ruta' => '/metodos-pago', 'icono' => 'wallet', 'grupo' => 'Catálogos', 'orden' => 7, 'roles' => ['Propietario']],
            ['nombre' => 'Tipos de vehículo', 'ruta' => '/tipos-vehiculo', 'icono' => 'car', 'grupo' => 'Catálogos', 'orden' => 8, 'roles' => ['Propietario']],
            ['nombre' => 'Franjas horarias', 'ruta' => '/franjas-horarias', 'icono' => 'clock', 'grupo' => 'Catálogos', 'orden' => 9, 'roles' => ['Propietario']],
            ['nombre' => 'Planes de pago', 'ruta' => '/planes-pago', 'icono' => 'list', 'grupo' => 'Catálogos', 'orden' => 10, 'roles' => ['Propietario']],
            ['nombre' => 'Tipos de curso', 'ruta' => '/tipos-curso', 'icono' => 'book-open', 'grupo' => 'Catálogos', 'orden' => 11, 'roles' => ['Propietario']],

            ['nombre' => 'Usuarios', 'ruta' => '/usuarios', 'icono' => 'users', 'grupo' => 'Administración', 'orden' => 12, 'roles' => ['Propietario', 'Secretaria']],
            ['nombre' => 'Vehículos', 'ruta' => '/vehiculos', 'icono' => 'truck', 'grupo' => 'Administración', 'orden' => 13, 'roles' => ['Propietario', 'Secretaria']],
            ['nombre' => 'Roles', 'ruta' => '/roles', 'icono' => 'shield', 'grupo' => 'Administración', 'orden' => 14, 'roles' => ['Propietario']],
            ['nombre' => 'Reportes', 'ruta' => '/reportes', 'icono' => 'bar-chart', 'grupo' => 'Administración', 'orden' => 15, 'roles' => ['Propietario']],
            ['nombre' => 'Bitácora', 'ruta' => '/bitacora', 'icono' => 'file-text', 'grupo' => 'Administración', 'orden' => 16, 'roles' => ['Propietario']],
        ];

        foreach ($items as $item) {
            $menu = Menu::updateOrCreate(
                ['ruta' => $item['ruta']],
                ['nombre' => $item['nombre'], 'icono' => $item['icono'], 'grupo' => $item['grupo'], 'orden' => $item['orden'], 'activo' => true]
            );

            // sync() (no syncWithoutDetaching): este seeder es la única fuente de verdad
            // de qué roles ven cada ítem, así que un rol quitado de la lista de arriba
            // debe perder el acceso al re-sembrar (p.ej. Estudiante ya no debe ver "Pagos").
            $rolIds = Rol::whereIn('nombre', $item['roles'])->pluck('id');
            $menu->roles()->sync($rolIds);
        }
    }
}
