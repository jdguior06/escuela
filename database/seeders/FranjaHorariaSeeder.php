<?php

namespace Database\Seeders;

use App\Models\FranjaHoraria;
use Illuminate\Database\Seeder;

class FranjaHorariaSeeder extends Seeder
{
    public function run(): void
    {
        $franjas = [
            ['hora_inicio' => '08:00', 'hora_fin' => '10:00'],
            ['hora_inicio' => '10:00', 'hora_fin' => '12:00'],
            ['hora_inicio' => '14:00', 'hora_fin' => '16:00'],
            ['hora_inicio' => '16:00', 'hora_fin' => '18:00'],
        ];

        foreach ($franjas as $franja) {
            FranjaHoraria::firstOrCreate($franja);
        }
    }
}
