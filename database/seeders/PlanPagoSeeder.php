<?php

namespace Database\Seeders;

use App\Models\PlanPago;
use Illuminate\Database\Seeder;

class PlanPagoSeeder extends Seeder
{
    public function run(): void
    {
        PlanPago::where('nombre', 'Pago único')->update(['nombre' => 'Contado']);
        PlanPago::where('nombre', '3 cuotas')->update(['nombre' => 'Crédito tres cuotas']);

        $planes = [
            ['nombre' => 'Contado', 'numero_cuotas' => 1, 'estado' => 'activo'],
            ['nombre' => 'Crédito dos cuotas', 'numero_cuotas' => 2, 'estado' => 'activo'],
            ['nombre' => 'Crédito tres cuotas', 'numero_cuotas' => 3, 'estado' => 'activo'],
        ];

        foreach ($planes as $plan) {
            PlanPago::updateOrCreate(['nombre' => $plan['nombre']], $plan);
        }
    }
}
