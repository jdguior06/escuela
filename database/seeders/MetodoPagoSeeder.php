<?php

namespace Database\Seeders;

use App\Models\MetodoPago;
use Illuminate\Database\Seeder;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $metodos = [
            'Efectivo' => 'Pago Físico',
            'QR' => 'Pago Digital',
        ];

        foreach ($metodos as $nombre => $descripcion) {
            MetodoPago::updateOrCreate(['nombre' => $nombre], ['descripcion' => $descripcion]);
        }

        MetodoPago::where('nombre', 'Transferencia')->delete();
    }
}
