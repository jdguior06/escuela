<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\FranjaHoraria;
use App\Models\TipoCurso;
use App\Models\Usuario;
use App\Models\Vehiculo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CursoSeeder extends Seeder
{
    public function run(): void
    {
        $franjas = FranjaHoraria::orderBy('hora_inicio')->get();

        $ramas = [
            ['correo' => 'instructor@grupo13sa.com', 'placa' => '1245-ABC', 'tipoCurso' => 'Curso de Auto Básico 10 Horas'],
            ['correo' => 'maria.fernandez@grupo13sa.com', 'placa' => '1489-BCD', 'tipoCurso' => 'Curso de Auto Avanzado 10 Horas'],
            ['correo' => 'jorge.quispe@grupo13sa.com', 'placa' => '2350-MTC', 'tipoCurso' => 'Curso de Moto Básico 10 Horas'],
            ['correo' => 'luis.mamani@grupo13sa.com', 'placa' => '2871-MTD', 'tipoCurso' => 'Curso de Moto Avanzado 10 Horas'],
            ['correo' => 'ana.vargas@grupo13sa.com', 'placa' => '3120-CAM', 'tipoCurso' => 'Curso de Camion Avanzado 10 Horas'],
        ];

        $fechaInicio = Carbon::now()->addWeek()->startOfWeek();
        $fechaFin = $fechaInicio->copy()->addDays(4);

        foreach ($ramas as $rama) {
            $instructor = Usuario::where('correo', $rama['correo'])->firstOrFail();
            $vehiculo = Vehiculo::where('placa', $rama['placa'])->firstOrFail();
            $tipoCurso = TipoCurso::where('nombre', $rama['tipoCurso'])->firstOrFail();

            foreach ($franjas as $franja) {
                Curso::updateOrCreate(
                    [
                        'instructor_id' => $instructor->id,
                        'vehiculo_id' => $vehiculo->id,
                        'tipo_curso_id' => $tipoCurso->id,
                        'franja_horaria_id' => $franja->id,
                    ],
                    [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                        'precio_final' => $tipoCurso->precio,
                        'estado_curso' => 'disponible',
                    ]
                );
            }
        }
    }
}
