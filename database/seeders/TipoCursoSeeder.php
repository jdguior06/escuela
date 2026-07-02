<?php

namespace Database\Seeders;

use App\Models\TipoCurso;
use App\Models\TipoVehiculo;
use Illuminate\Database\Seeder;

class TipoCursoSeeder extends Seeder
{
    public function run(): void
    {
        $auto = TipoVehiculo::where('nombre', 'Auto')->firstOrFail();
        $moto = TipoVehiculo::where('nombre', 'Moto')->firstOrFail();
        $camion = TipoVehiculo::where('nombre', 'Camion')->firstOrFail();

        $cursos = [
            [
                'nombre' => 'Curso de Auto Básico 10 Horas',
                'descripcion' => 'Curso de Vehículo Básico',
                'precio' => 1000,
                'estado_curso' => 'activo',
                'duracion_horas' => 10,
                'tipo_vehiculo_id' => $auto->id,
            ],
            [
                'nombre' => 'Curso de Auto Avanzado 10 Horas',
                'descripcion' => 'Curso de Vehículo Medio',
                'precio' => 1500,
                'estado_curso' => 'activo',
                'duracion_horas' => 10,
                'tipo_vehiculo_id' => $auto->id,
            ],
            [
                'nombre' => 'Curso de Moto Básico 10 Horas',
                'descripcion' => 'Curso de Moto',
                'precio' => 1000,
                'estado_curso' => 'activo',
                'duracion_horas' => 10,
                'tipo_vehiculo_id' => $moto->id,
            ],
            [
                'nombre' => 'Curso de Moto Avanzado 10 Horas',
                'descripcion' => 'Curso de Vehículo Básico',
                'precio' => 1500,
                'estado_curso' => 'activo',
                'duracion_horas' => 10,
                'tipo_vehiculo_id' => $moto->id,
            ],
            [
                'nombre' => 'Curso de Camion Avanzado 10 Horas',
                'descripcion' => 'Curso de Vehículo Avanzado',
                'precio' => 2000,
                'estado_curso' => 'activo',
                'duracion_horas' => 10,
                'tipo_vehiculo_id' => $camion->id,
            ],
        ];

        foreach ($cursos as $curso) {
            TipoCurso::updateOrCreate(['nombre' => $curso['nombre']], $curso);
        }
    }
}
