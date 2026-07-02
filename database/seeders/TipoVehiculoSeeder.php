<?php

namespace Database\Seeders;

use App\Models\TipoVehiculo;
use Illuminate\Database\Seeder;

class TipoVehiculoSeeder extends Seeder
{
    public function run(): void
    {
        TipoVehiculo::where('nombre', 'Automóvil')->update(['nombre' => 'Auto']);
        TipoVehiculo::where('nombre', 'Motocicleta')->update(['nombre' => 'Moto']);

        $tipos = [
            'Auto' => 'Vehículo particular y liviano',
            'Moto' => 'Vehículo de dos ruedas',
            'Camion' => 'Vehículo particular y pesado',
        ];

        foreach ($tipos as $nombre => $descripcion) {
            TipoVehiculo::updateOrCreate(['nombre' => $nombre], ['descripcion' => $descripcion]);
        }
    }
}
