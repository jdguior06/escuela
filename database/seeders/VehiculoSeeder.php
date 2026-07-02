<?php

namespace Database\Seeders;

use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Database\Seeder;

class VehiculoSeeder extends Seeder
{
    public function run(): void
    {
        $auto = TipoVehiculo::where('nombre', 'Auto')->firstOrFail();
        $moto = TipoVehiculo::where('nombre', 'Moto')->firstOrFail();
        $camion = TipoVehiculo::where('nombre', 'Camion')->firstOrFail();

        $vehiculos = [
            ['placa' => '1245-ABC', 'marca' => 'Toyota', 'modelo' => 'Yaris', 'tipo_vehiculo_id' => $auto->id],
            ['placa' => '1489-BCD', 'marca' => 'Suzuki', 'modelo' => 'Baleno', 'tipo_vehiculo_id' => $auto->id],
            ['placa' => '2350-MTC', 'marca' => 'Honda', 'modelo' => 'CB 125F', 'tipo_vehiculo_id' => $moto->id],
            ['placa' => '2871-MTD', 'marca' => 'Yamaha', 'modelo' => 'FZ 2.0', 'tipo_vehiculo_id' => $moto->id],
            ['placa' => '3120-CAM', 'marca' => 'Hino', 'modelo' => '300', 'tipo_vehiculo_id' => $camion->id],
        ];

        foreach ($vehiculos as $vehiculo) {
            Vehiculo::updateOrCreate(
                ['placa' => $vehiculo['placa']],
                $vehiculo + ['estado_vehiculo' => 'disponible']
            );
        }
    }
}
